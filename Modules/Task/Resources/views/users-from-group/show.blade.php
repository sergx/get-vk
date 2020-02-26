@extends('layouts.app')

@section('content')
<div class="container">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('project.index')}}">Проекты</a></li>
      <li class="breadcrumb-item"><a href="{{route('project.show', $task->project->id)}}">{{$task->project->name}}</a></li>
      <li class="breadcrumb-item active" aria-current="page">Задание</li>
    </ol>
  </nav>
  <h1>Задание</h1>

  <p><strong>Входные данные:</strong></p>
  <ul>
    <li><strong>Поисковый запрос</strong> <span>{{$task_data['search_query']}}</span></li>
    <li><strong>Тип поиска</strong> <span>{{ intval($task_data['group_search_type']) }}</span></li>
    <li><strong>Лимит</strong> <span>{{ intval($task_data['limit']) }}</span></li>
  </ul>
<pre>
Хорошо бы еще получить информацию о том - сколько всего групп найдено
</pre>
  <table>
    <tbody>
      @foreach ($task->vk_groups as $item)
        <tr>
          <td>{{$item->id}}</td>
          <td>{{$item->name}}</td>
          <td><a href="https://vk.com/{{$item->screen_name}}" target="_blank">{{$item->screen_name}}</a></td>
          <td>{{$item->is_closed}}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
