@extends('master.layouts.master')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center my-3">
            <h2>分类管理</h2>
            <a href="{{ route('master.categories.create') }}" class="btn btn-primary">添加分类</a>
        </div>

        {{-- 成功消息 --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- 错误消息 --}}
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- 分类列表 --}}
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>分类名称</th>
                    <th>Slug</th>
                    <th>排序</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ $category->sort_order }}</td>
                        <td>
                            @if($category->active)
                                <span class="badge bg-success">启用</span>
                            @else
                                <span class="badge bg-danger">禁用</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('master.categories.edit', $category->id) }}" class="btn btn-sm btn-warning">编辑</a>
                            <form action="{{ route('master.categories.destroy', $category->id) }}" method="POST" style="display:inline-block;" 
                                onsubmit="return confirm('确定要删除这个分类吗？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">删除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $categories->links() }}
        </div>
    </div>
@endsection
