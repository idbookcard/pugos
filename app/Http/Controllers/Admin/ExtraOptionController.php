<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtraOption;
use App\Services\SEOeStoreApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExtraOptionController extends Controller
{
    /**
     * 显示额外选项列表
     */
    public function index()
    {
        $extras = ExtraOption::orderBy('name')->paginate(20);
        return view('master.extras.index', compact('extras'));
    }
    
    /**
     * 显示创建额外选项页面
     */
    public function create()
    {
        return view('master.extras.create');
    }
    
    /**
     * 保存新创建的额外选项
     */
    public function store(Request $request)
    {
        $request->validate([
            'extra_id' => 'required|string|unique:extra_options',
            'code' => 'required|string',
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'is_multiple' => 'boolean',
            'active' => 'boolean'
        ]);
        
        ExtraOption::create([
            'extra_id' => $request->extra_id,
            'code' => $request->code,
            'name' => $request->name,
            'name_zh' => $request->name_zh ?? $request->name,
            'price' => $request->price,
            'is_multiple' => $request->is_multiple ?? false,
            'active' => $request->active ?? true
        ]);
        
        return redirect()->route('master.extras.index')
            ->with('success', '额外选项创建成功');
    }
    
    /**
     * 显示编辑额外选项页面
     */
    public function edit($id)
    {
        $extra = ExtraOption::findOrFail($id);
        return view('master.extras.edit', compact('extra'));
    }
    
    /**
     * 更新额外选项
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'is_multiple' => 'boolean',
            'active' => 'boolean'
        ]);
        
        $extra = ExtraOption::findOrFail($id);
        
        $extra->update([
            'code' => $request->code,
            'name' => $request->name,
            'name_zh' => $request->name_zh ?? $extra->name_zh,
            'price' => $request->price,
            'is_multiple' => $request->is_multiple ?? false,
            'active' => $request->active ?? true
        ]);
        
        return redirect()->route('master.extras.index')
            ->with('success', '额外选项更新成功');
    }
    
    /**
     * 删除额外选项
     */
    public function destroy($id)
    {
        $extra = ExtraOption::findOrFail($id);
        
        // 检查是否有关联的产品
        if ($extra->packages()->count() > 0) {
            return redirect()->route('master.extras.index')
                ->with('error', '该额外选项已关联产品，无法删除');
        }
        
        $extra->delete();
        
        return redirect()->route('master.extras.index')
            ->with('success', '额外选项已删除');
    }
    
    /**
     * 从API同步额外选项
     */
    public function sync()
    {
        try {
            $apiService = new SEOeStoreApiService();
            $extras = $apiService->getExtras();
            
            if (empty($extras)) {
                return redirect()->route('master.extras.index')
                    ->with('error', '无法从API获取额外选项数据');
            }
            
            $count = 0;
            
            foreach ($extras as $extra) {
                ExtraOption::updateOrCreate(
                    ['extra_id' => $extra['id']],
                    [
                        'code' => $extra['code'],
                        'name' => $extra['description'],
                        'name_zh' => $extra['description'] . ' (待翻译)',
                        'price' => floatval($extra['price']),
                        'is_multiple' => $extra['multiple'] == '1',
                        'active' => true
                    ]
                );
                $count++;
            }
            
            return redirect()->route('master.extras.index')
                ->with('success', "成功同步 {$count} 个额外选项");
        } catch (\Exception $e) {
            Log::error('额外选项同步失败: ' . $e->getMessage());
            
            return redirect()->route('master.extras.index')
                ->with('error', '同步失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 显示手动导入页面
     */
    public function showImport()
    {
        return view('master.extras.import');
    }
    
    /**
     * 处理手动导入
     */
    public function import(Request $request)
    {
        $request->validate([
            'json_data' => 'required|string'
        ]);
        
        try {
            $data = json_decode($request->json_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->route('master.extras.import')
                    ->with('error', 'JSON数据格式错误: ' . json_last_error_msg());
            }
            
            $apiService = new SEOeStoreApiService();
            $result = $apiService->importExtras($data);
            
            if ($result['success']) {
                return redirect()->route('master.extras.index')
                    ->with('success', $result['message']);
            } else {
                return redirect()->route('master.extras.import')
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('额外选项导入失败: ' . $e->getMessage());
            
            return redirect()->route('master.extras.import')
                ->with('error', '导入失败: ' . $e->getMessage());
        }
    }

    /**
     * API: 获取所有额外选项列表
     */
    public function apiList()
    {
        try {
            $extras = ExtraOption::where('active', true)
                ->orderBy('name')
                ->get();
                
            return response()->json([
                'success' => true,
                'extras' => $extras
            ]);
        } catch (\Exception $e) {
            Log::error('获取额外选项API列表失败: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
/**
     * 批量更新额外选项状态
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'active' => 'required|boolean'
        ]);
        
        try {
            $count = ExtraOption::whereIn('id', $request->ids)
                ->update(['active' => $request->active]);
                
            return response()->json([
                'success' => true,
                'message' => '已成功更新 ' . $count . ' 个额外选项的状态',
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('更新额外选项状态失败: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '更新失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 批量更新额外选项价格
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePrices(Request $request)
    {
        $request->validate([
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0'
        ]);
        
        try {
            $updated = 0;
            
            foreach ($request->prices as $id => $price) {
                $extra = ExtraOption::find($id);
                if ($extra) {
                    $extra->price = $price;
                    $extra->save();
                    $updated++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => '已成功更新 ' . $updated . ' 个额外选项的价格',
                'count' => $updated
            ]);
        } catch (\Exception $e) {
            Log::error('更新额外选项价格失败: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '更新失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 翻译额外选项名称
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function translate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:extra_options,id'
        ]);
        
        try {
            $extra = ExtraOption::findOrFail($request->id);
            
            // 调用翻译服务
            // 这里可以集成百度翻译、Google翻译等API
            // 简单示例使用内置函数
            $translated = $this->simpleTranslate($extra->name);
            
            // 更新翻译
            $extra->name_zh = $translated;
            $extra->save();
            
            return response()->json([
                'success' => true,
                'message' => '翻译成功',
                'original' => $extra->name,
                'translated' => $translated
            ]);
        } catch (\Exception $e) {
            Log::error('翻译额外选项失败: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '翻译失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 简单翻译函数（示例）
     * 在实际应用中，建议使用专业翻译API
     */
    private function simpleTranslate($text)
    {
        // 简单的英文术语翻译映射
        $translations = [
            'indexer' => '索引器',
            'backlinks' => '反向链接',
            'article' => '文章',
            'social' => '社交',
            'signals' => '信号',
            'content' => '内容',
            'quality' => '质量',
            'high' => '高',
            'maximum' => '最大',
            'rate' => '比率',
            'crawled' => '爬取',
            'design' => '设计',
            'image' => '图像',
            'submission' => '提交',
            'visits' => '访问量',
            'human' => '人工',
            'link' => '链接',
            'custom' => '自定义'
        ];
        
        // 替换关键词
        $result = $text;
        foreach ($translations as $en => $zh) {
            $result = str_ireplace($en, $zh, $result);
        }
        
        // 如果没有变化，添加通用前缀
        if ($result === $text) {
            $result = $text . ' (待翻译)';
        }
        
        return $result;
    }
}