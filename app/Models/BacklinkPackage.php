namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BacklinkPackage extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'name_en',
        'description',
        'package_type',
        'price',
        'delivery_days',
        'required_fields',
        'third_party_id',
        'third_party_service_id',
        'guest_post_url',
        'guest_post_da',
        'is_featured',
        'is_active',
        'original_item_data',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'required_fields' => 'json',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'original_item_data' => 'json',
    ];
    
    public function orders()
    {
        return $this->hasMany(Order::class, 'package_id');
    }
    
    public function scopeMonthly($query)
    {
        return $query->where('package_type', 'monthly')->where('is_active', true);
    }
    
    public function scopeSingle($query)
    {
        return $query->where('package_type', 'single')->where('is_active', true);
    }
    
    public function scopeThirdParty($query)
    {
        return $query->where('package_type', 'third_party')->where('is_active', true);
    }
    
    public function scopeGuestPost($query)
    {
        return $query->where('package_type', 'guest_post')->where('is_active', true);
    }
}