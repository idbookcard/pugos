<?php
// app/Providers/HelpersServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelpersServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Load helpers file
        require_once app_path('Helpers/AppHelpers.php');
        
        // Register system settings in config
        $this->loadSystemSettings();
        
        // Register custom blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
    
    /**
     * Load system settings from database into config
     */
    protected function loadSystemSettings()
    {
        if (!\Schema::hasTable('system_settings')) {
            return;
        }
        
        try {
            // Get settings from cache or database
            $settings = \Cache::remember('system_settings', 3600, function () {
                return \App\Models\SystemSetting::all();
            });
            
            // Add settings to config
            foreach ($settings as $setting) {
                config(['pugos.settings.' . $setting->setting_key => $setting->setting_value]);
            }
            
            // Set admin emails
            $adminEmails = config('pugos.settings.admin_emails');
            if ($adminEmails) {
                $emails = is_array($adminEmails) ? $adminEmails : explode(',', $adminEmails);
                config(['pugos.admin_emails' => $emails]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load system settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Register custom blade directives
     */
    protected function registerBladeDirectives()
    {
        // Format status with badge
        \Blade::directive('statusBadge', function ($expression) {
            return "<?php echo '<span class=\"badge ' . 
                \App\Helpers\AppHelpers::formatStatus({$expression})['class'] . 
                '\">' . 
                \App\Helpers\AppHelpers::formatStatus({$expression})['label'] . 
                '</span>'; ?>";
        });
        
        // Format price with currency
        \Blade::directive('price', function ($expression) {
            return "<?php echo \App\Helpers\AppHelpers::formatPrice({$expression}); ?>";
        });
        
        // Format package type
        \Blade::directive('packageType', function ($expression) {
            return "<?php echo \App\Helpers\AppHelpers::formatPackageType({$expression}); ?>";
        });
        
        // Truncate text
        \Blade::directive('truncate', function ($expression) {
            $args = explode(',', str_replace(['(', ')', ' '], '', $expression));
            $text = $args[0];
            $length = isset($args[1]) ? $args[1] : 100;
            
            return "<?php echo \App\Helpers\AppHelpers::truncate({$text}, {$length}); ?>";
        });
    }
}