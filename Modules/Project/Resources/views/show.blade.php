@extends('layouts.app')

@section('content')
<div class="container">
  @include('vkapi::inc.vk_token')
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('project.index')}}">Проекты</a></li>
      <li class="breadcrumb-item active" aria-current="page">{{$project->name}}</li>
    </ol>
  </nav>

  <h1>{{$project->name}}</h1>
  

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Задания</h5>
      <div class="btn-group">
        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Добавить
        </button>
        <div class="dropdown-menu">
            @foreach ($task_routs as $item)
            <a class="dropdown-item" href="{{$item->route}}">{{$item->name}}</a>
            @endforeach
        </div>
      </div>
    </div>
    <div class="card-body">
      @if(count($tasks) > 0)
      <ul class="list-group list-group-flush">
        @foreach ($tasks as $item)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <a href="{{route('task.'.$item->task_key.'.show', ['project_id' => $project->id, 'task_id' => $item->id])}}">{{$item->name}}</a>
          
          {!! Form::open(['route' => ['task.destroy', $project->id, $item->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
            {{Form::hidden('_method', 'DELETE')}}
            {{Form::submit('Удалить', ['class' => 'btn btn-danger btn-sm'])}}
          {!! Form::close() !!}
        </li>
        @endforeach
      </ul>
      @endif
    </div>
  </div>
  


<pre>
Задания
 - Поиск групп
 - Парсинг участников групп
 - Парсинг постов и лайков в группах
 - Поиск вовлеченных пользователей - по числу пересечек
 - Поиск вовлеченных пользователей - по активности (лайки/репосты)

Чтобы добавить задание нужно перейти на спец. страницу, и там дудут варианты - какие задания можно добавить

На странице проекта в табличке отображаются название задания, статус и если результаты, то какие-то специфические ссылки, например на формирование следующего задания.

Примеры:

Поиск групп: "Сварка"
—————
Найдено 2231 гр.
> посмотреть список групп (и отсеить лишние)
> спарсить участников всех
> спарсить участников выборочно (посмотреть список групп)
—————
Статус задачи


При добавлении задачи:

Поиск групп:
[ поисковый запрос ]
—————
Выбор:
- Максимально много
- 1000 самых релевантных
- Указать кол-во самых релевантных


Когда есть список групп, мы можем посмотреть список групп и отметить те группы, участников которых хотим спарсить

Табличка с чекбоксами по 25 штук сразу (и возможностью индивидуально выбрать тоже)



Когда у нас есть список пользователей групп:
Задача - найти пересечки у аудитории

Отметить группы, в которых искать пересечки

Алгоритм:
 - учитывать кол-во человек в группе в принципе





- Тип задания (название)
- Входные данные - либо указываются пользователем (например, поисковый запрос), или выбор - "какую группу парсим", или просто "запустить", когда нужно "запарсить все", или типа того
- Статус/результат работы

</pre>


{{--
  {!! Form::open(['route' => ['project.update', $project->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
  {{Form::hidden('_method', 'PUT')}}
  <div class="form-group">
    <div class="form-row">
      <div class="col">
        {{Form::label('name','Название')}}
        {{Form::text('name', $project->name, ['class' => 'form-control','placeholder' => 'Название'])}}
      </div>
    </div>
  </div>
  {{Form::submit('Сохранить', ['class' => 'btn btn-primary'])}}
  {!! Form::close() !!}
--}}
</div>
@endsection
