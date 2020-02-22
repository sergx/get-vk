@if(session('vk-token') && time() < session('vk-token-expires_in'))
<div class="alert alert-success mb-3">
  Токен есть. Истекает через {{gmdate("d д. H:i:s", session('vk-token-expires_in') - time())}}
  <a href="{{route('vkapi.oauth')}}" class="btn btn-primary btn-sm ml-2">Обновить токен</a>
</div>
@else
<div class="alert alert-warning">
Токена нет
<a href="{{route('vkapi.oauth')}}" class="btn btn-primary btn-sm ml-2">Получить токен</a>
</div>

@endif
