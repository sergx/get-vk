@extends('layouts.app')

@section('content')
<div class="container">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('project.index')}}">Проекты</a></li>
      <li class="breadcrumb-item"><a href="{{route('project.show', $project->id)}}">{{$project->name}}</a></li>
      <li class="breadcrumb-item active" aria-current="page">Добавить задание</li>
    </ol>
  </nav>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Варианты заданий</h5>
    </div>
    <div class="card-body">
      @if(count($task_routs) > 0)
      <ul class="list-group list-group-flush">
        @foreach ($task_routs as $item)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <a href="{{$item->route}}">{{$item->name}}</a>
        </li>
        @endforeach
      </ul>
      @endif
    </div>
  </div>
</div>
@endsection
