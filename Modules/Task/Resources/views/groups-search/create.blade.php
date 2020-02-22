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
  <h1>Добавить задание</h1>
  
  {!! Form::open(['route' => ['task.groups-search.store',$project->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
  {{Form::hidden('project_id', $project->id)}}

  <div class="form-group">
    <div class="form-row">
      <div class="col-6">
        {{Form::label('search_query','Поисковый запрос. Желательно 1 слово')}}
        {{Form::text('search_query', '', ['class' => 'form-control','placeholder' => 'Поисковый запрос'])}}
      </div>
      <div class="col">
        {{Form::label('group_search_type','Тип поиска')}}
        <select class="form-control" id="group_search_type">
          {{--<option value="0">По умолчанию (как в результатах поиска на сайте)</option>--}}
          <option value="1">Максимум</option>
        </select>
      </div>
      <div class="col-2">
        {{Form::label('limit','Лимит')}}
        {{Form::number('limit', '', ['class' => 'form-control','placeholder' => 'Лимит'])}}
      </div>
    </div>
  </div>

  {{Form::submit('Собрать', ['class' => 'btn btn-primary'])}}
  {!! Form::close() !!}

<pre>
Разные типы задания - разные контроллеры?
Уж точно разные обработчики
</pre>
</div>
@endsection
