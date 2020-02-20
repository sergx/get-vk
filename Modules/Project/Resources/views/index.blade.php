@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Проекты</h5>
          <a class="btn btn-primary btn-sm" href="{{route('project.create')}}" role="button">Добавить</a>
        </div>
        <div class="card-body">
          @if(count($projects) > 0)
          <ul class="list-group list-group-flush">
            @foreach ($projects as $item)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <a href="{{route('project.show', ['id' => $item->id])}}">{{$item->name}}</a>

              {!! Form::open(['route' => ['project.destroy', $item->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                {{Form::hidden('_method', 'DELETE')}}
                {{Form::submit('Удалить', ['class' => 'btn btn-warning btn-sm'])}}
              {!! Form::close() !!}
            </li>
            @endforeach
          </ul>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
