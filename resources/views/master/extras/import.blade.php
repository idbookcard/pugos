{{-- resources/views/master/extras/import.blade.php --}}
@extends('master.layouts.master')

@section('title', '导入额外选项')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">导入额外选项</h3>
                </div>
                
                <form action="{{ route('master.extras.import') }}" method="POST">
                    @csrf
                    
                    <div class="card-body">
                        <div class="callout callout-info">
                            <h5>导入说明</h5>
                            <p>通过JSON数据导入额外选项，适用于API接口不提供直接获取选项的情况。</p>
                            <p>JSON格式应为数组，每个选项包含以下字段：</p>
                            <ul>
                                <li><strong>id</strong>: API中的选项ID</li>
                                <li><strong>code</strong>: 选项代码</li>
                                <li><strong>description</strong>: 选项名称/描述</li>
                                <li><strong>price</strong>: 选项价格</li>
                                <li><strong>multiple</strong>: 是否支持多选（1表示是，0表示否）</li>
                            </ul>
                        </div>
                        
                        <div class="form-group">
                            <label for="json_data">JSON数据 <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('json_data') is-invalid @enderror" 
                                      id="json_data" name="json_data" rows="15" required>{{ old('json_data') }}</textarea>
                            @error('json_data')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <button type="button" id="format_json" class="btn btn-default">
                                <i class="fas fa-code"></i> 格式化JSON
                            </button>
                            <button type="button" id="validate_json" class="btn btn-default">
                                <i class="fas fa-check-circle"></i> 验证JSON
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">导入数据</button>
                        <a href="{{ route('master.extras.index') }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
            
            <!-- 示例数据 -->
            <div class="card collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">示例数据</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
<pre><code>[
  {
    "id": "1",
    "code": "i",
    "description": "Indexer #1 (95%+ Crawled rate)",
    "price": "0.01",
    "multiple": "1"
  },
  {
    "id": "2",
    "code": "i2",
    "description": "Indexer #2 (Very High indexer rate)",
    "price": "0.10",
    "multiple": "1"
  }
]</code></pre>
                    <button type="button" id="use_example" class="btn btn-default btn-sm">
                        <i class="fas fa-copy"></i> 使用此示例
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 格式化JSON
        document.getElementById('format_json').addEventListener('click', function() {
            const jsonInput = document.getElementById('json_data');
            try {
                const jsonData = JSON.parse(jsonInput.value);
                jsonInput.value = JSON.stringify(jsonData, null, 2);
            } catch (e) {
                alert('JSON格式错误: ' + e.message);
            }
        });
        
        // 验证JSON
        document.getElementById('validate_json').addEventListener('click', function() {
            const jsonInput = document.getElementById('json_data');
            try {
                const jsonData = JSON.parse(jsonInput.value);
                
                if (!Array.isArray(jsonData)) {
                    throw new Error('数据必须是数组格式');
                }
                
                if (jsonData.length === 0) {
                    throw new Error('数组不能为空');
                }
                
                // 验证每个项目
                jsonData.forEach((item, index) => {
                    if (!item.id) {
                        throw new Error(`第${index+1}项缺少id字段`);
                    }
                    if (!item.description) {
                        throw new Error(`第${index+1}项缺少description字段`);
                    }
                });
                
                alert('JSON格式验证通过，包含 ' + jsonData.length + ' 个选项');
            } catch (e) {
                alert('JSON验证失败: ' + e.message);
            }
        });
        
        // 使用示例
        document.getElementById('use_example').addEventListener('click', function() {
            const exampleJson = `[
  {
    "id": "1",
    "code": "i",
    "description": "Indexer #1 (95%+ Crawled rate)",
    "price": "0.01",
    "multiple": "1"
  },
  {
    "id": "2",
    "code": "i2",
    "description": "Indexer #2 (Very High indexer rate)",
    "price": "0.10",
    "multiple": "1"
  }
]`;
            document.getElementById('json_data').value = exampleJson;
        });
    });
</script>
@endsection