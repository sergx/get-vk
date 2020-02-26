<?php

namespace Modules\VkApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

use Modules\VkApi\Entities\RequestHistory;

class VkApiController extends Controller
{

  private $access_token;
  private $api_version = "5.103";
  private $url = 'https://api.vk.com/method/';

  private $groups_added = 0;

  // Конструктор
  public function __construct()
  {
    // Конструкция для того чтобы получить доступ к сессии и пользователю из конструктора
    $this->middleware(function ($request, $next)
    {
      $this->access_token = session('vk-token');
      return $next($request);
    });
  }


  public function vkrSdk($method, $params = null){

  }
  /**
   * Делает запрос к API VK
   * @param $method
   * @param $params
   */
  public function vkr($method, $params = null)
  {
    $p = '';

    $params['access_token'] = session('vk-token');
    $params['v'] = $this->api_version;
    //dd($params);
    if ($params && is_array($params))
    {
      foreach ($params as $key => $param)
      {
        $p .= ($p == '' ? '' : '&') . $key . '=' . urlencode($param);
      }
    }

    $curl_handle = curl_init();
    $requrest_url = $this->url . $method /* . '?' . 'access_token=' . session('vk-token') . '&v=' . $this->api_version*/;
    //$requrest_url = $this->url . $method . '?' . ($p ? $p . '&' : '') . 'access_token=' . session('vk-token') . '&v=' . $this->api_version;
    
    // Попытка взять из кэша
    //$cache_key = 'vk_'.md5($this->url . $method . '?' . ($p ? $p . '&' : '') . '&v=' . $this->api_version);
    // if (Cache::has($cache_key)) {
    //   return Cache::get($cache_key);
    // }

    curl_setopt($curl_handle, CURLOPT_URL, $requrest_url);
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $p);
    curl_setopt($curl_handle, CURLOPT_POST, 1);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($curl_handle);

    $result_code = curl_getinfo($curl_handle, CURLINFO_RESPONSE_CODE);
    $result_length = curl_getinfo($curl_handle, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

    curl_close($curl_handle);

    if ($response)
    {
      //dd($response);
      // запись в кэш
      //Cache::forever($cache_key, $response);

      // Запись в "лог"
      $VkRequestHistory = new RequestHistory;
      $VkRequestHistory->url = $requrest_url;
      $VkRequestHistory->method = $method;
      //$VkRequestHistory->cache_key = $cache_key;
      $VkRequestHistory->params = json_encode($params, 256);
      $VkRequestHistory->result_code = $result_code;
      $VkRequestHistory->result_length = $result_length;
      if($result_length < 2500)
      {
        $VkRequestHistory->result = $response;
      }
      $VkRequestHistory->save();

      if($response == "ERROR")
      {
        if(session("reduce_limit")){
          session(["reduce_limit" => session("reduce_limit") -1 ]);
        }else{
          session(["reduce_limit" => 20]);
        }
        //throw new \Illuminate\Http\Exceptions\HttpResponseException(redirect('http://get-vk.test/project/2/task/users-from-group/37/prossess?groups_from_task_id=29'));
        throw new \Illuminate\Http\Exceptions\HttpResponseException(redirect(request()->getRequestUri()));
        //return redirect(request()->getRequestUri());
      }
      session()->forget('reduce_limit');

      return $response;
    }

    return false;
  }


  public function vkOauth(Request $request)
  {
    $client_id = 7314679; // ID Приложения
    $redirect_uri = "http://get-vk.test/vkapi/oauth"; // Должен быть один и тот же для обоих методов

    if(!empty($request->code)){
      $oauth = new \VK\OAuth\VKOAuth();
      $client_secret = "5soN20VECEskedHcHAgI"; // В настройках приложения на сайте VK
      $code = $request->code;
      
      $response = $oauth->getAccessToken($client_id, $client_secret, $redirect_uri, $code);
      session(['vk-token' => $response['access_token']]);
      session(['vk-token-expires_in' => time() + $response['expires_in']]);

      // Для автоматического продления сессии
      if(session('vk-token-expires_requestUri')){
        $requestUri = session('vk-token-expires_requestUri');
        session()->forget('vk-token-expires_requestUri');
        return redirect(session('vk-token-expires_requestUri'));
      }

      return redirect()->route("project.index");
    }else{
      $oauth = new \VK\OAuth\VKOAuth();
      $display = \VK\OAuth\VKOAuthDisplay::PAGE;
      $scope = array(
        \VK\OAuth\Scopes\VKOAuthUserScope::WALL,
        \VK\OAuth\Scopes\VKOAuthUserScope::GROUPS,
        \VK\OAuth\Scopes\VKOAuthUserScope::FRIENDS,
  
      );
      $state = 'secret_state_code';
  
      $browser_url = $oauth->getAuthorizeUrl(\VK\OAuth\VKOAuthResponseType::CODE, $client_id, $redirect_uri, $display, $scope, $state);
      return redirect($browser_url);
    }
  }


  // отсылается POST запрос с полученым кокеном
  public function tokenSave(Request $request){
    // Создать таблицу, в которой хранится токен и указано время expire
    $url = parse_url($request->input('vk-token-url'));
    $params = [];
    foreach(explode("&", str_replace('#', '', $url["fragment"])) as $parametr){
      $parametr = explode("=", $parametr);
      $params[$parametr[0]] = $parametr[1];
    }
    session(['vk-token' => $params['access_token']]);
    session(['vk-token-expires_in' => time() + $params['expires_in']]);
    return redirect()->route('project.index');
  }
}


/*
    $code = '
    var params = %s;
    //var params = {"q":"Сварка","count":250,"offset":0};
    var chunk_size = params.count;
    var result = [];
    var test1 = [];
    result.push(API.groups.search(params));
    var items_count = result[0]["items"].length;

    if(result[0].count > chunk_size){
      var elems_left = result[0].count - chunk_size;
      var call_id = 1;
      while(elems_left > 0){
        if(call_id > 24){
          result.push({"need_more": (params.offset + chunk_size)});
          return result;
        }
        if(items_count >= 1000){
          result.push({"need_more": (params.offset + chunk_size)});
          return result;
        }
        params.offset = params.offset + chunk_size;
        result.push(API.groups.search(params));
        items_count = items_count + result[call_id]["items"].length;
        call_id = call_id + 1;
        elems_left = elems_left - chunk_size;
      }
    }
    return result;
    ';
*/
