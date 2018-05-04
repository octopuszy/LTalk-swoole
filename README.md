# Ltalk


- 前端项目地址：https://github.com/LTalkTeam/LTalkHTML

- 在线预览：http://118.24.77.25/LTalkHTML/login.html

- 联系方式：octopus.zy.cn@gmail.com


### 本项目v1版本有以下功能：
- [x] 登录 
- [x] 注册
- [x] 添加好友与处理好友请求
- [x] 好友列表
- [x] 好友即时聊天
- [x] 世界聊天模块（所有在线人员）
- [x] 创建群组
- [x] 群组列表
- [x] 加入群组
- [x] 群组及时聊天
- [x] 好友离线上线提醒
- [x] 统计功能（当前在线总人数，基于日志的流量统计等）

### v2 或以后版本会完善
- 删除好友
- 退出群组
- 离线接受好友请求
- 接收离线消息
- 查看历史聊天记录



## 目录结构

目录结构如下：

~~~
LTalk  WEB部署目录
├─app                     应用目录
│  ├─Exception            自定义异常
│  ├─HttpController       HttpApi 控制器目录
│  │  ├─Common.php        公共方法
│  │  └─Router.php        自定义路由
│  │
│  ├─Model                tp orm 
│  ├─Service              服务层
│  ├─Sock                 websocket 输入输出规范配置
│  ├─Task                 异步Task方法模块
│  ├─Utility              进程池
│  ├─Validate             自定义验证层模块
│  └─WebsocketController  Websocket Api 控制器目录
...
(其他为 easyswoole 框架自带)
~~~

## 项目整体功能说明

- 基于 token 验证与存储实现登录与信息的初始化，当用户登录成功，生成并返回给用户 token，此后的所有请求，都带着此 token 来证明身份
- 聊天页面需要执行 init() 进行缓存的初始化，保存用户信息，token，以及 fd 的绑定，像所有好友推送上线消息
- 聊天，加好友，处理好友请求等操作，都以固定格式，推送消息到服务器，服务器转到相应 websocket 控制器去处理，返回结果
- 当用户关闭页面触发 onclose ，对缓存进行销毁

## 实现原理

本项目可分为两大类，登录与注册用 Http 基础应用实现，采用 api 主聊天页面的所有功能都通过 easyswoole 的 WebSocket 应用模块来实现。由于
EasySwoole 专为api而生，本项目不论是基于 http 还是websocket的应用功能，都是采用 api 接口形式请求与返回。

### Http 基础应用的关键代码分析

```
// HttpController/Api/Login.pho

public function login(){
    (new LoginValidate())->goCheck($this->request());       // 自定义验证类

   ...

    // 查询用户是否已经存在
    $user = UserModel::getUser(['email'=>$email]);          // tp orm 模型
    if(empty($user)){
        throw new LoginException([                          // 自定义全局异常类
            'msg'=>'无效账号',
            'errorCode'=>30001
        ]);
    }

    ...

    // 返回 token
    $this->writeJson(200, $token);
}
```

#### 自定义验证类

App/Validate 是验证类的存放目录，BaseValidate 是验证类的基类，封装了 goCheck 方法，可以自动验证传入参数是否符合子类定义的规则。

#### 自定义全局异常类

App/Exception 是异常类的存放目录，自定义异常的意义在于选择性把错误信息传给客户端，用户导致的异常如参数错误，是应该让客户端知道的，而程序错误或数据库错误等是不必要让客户端知道的，通过对异常处理的封装，就可以返回用户友好型结果，并且统一格式，遵循 RESTful 规范。

```
// 首先需要在 EasySwooleEvent 中注入异常处理类
static public function frameInitialize(): void
{
    // 设置全局异常处理类
    Di::getInstance()->set( SysConst::HTTP_EXCEPTION_HANDLER, \App\Exception\ExceptionHandel::class );
}

// 实现异常处理（ExceptionHandel.php）
public function handle( \Throwable $exception, Request $request, Response $response )
{
    // 若是用户操作导致的错误都是自定义的，继承自BaseException，可用于类型区别
    if( $exception instanceof BaseException ){
        // 自定义异常，返回给客户具体信息
        $this->code = $exception->code;
        $this->msg  = $exception->msg;
        $this->errorCode = $exception->errorCode;
    }else{
        $debug = Config::getInstance()->getConf('DEBUG');     // 若在调试模式下，则打印错误信息，否则记录日志，返回500
        $this->code =500;
        $this->errorCode = 999;
        $this->recordErrorLog($exception);
        if($debug){                                         
            $this->msg = $exception->getMessage();
        }else{
            $this->msg = '服务器错误';
            $this->recordErrorLog($exception);
        }
    }
    $this->returnJson($response);
}
```

### websocket 应用关键代码分析

前端请求格式：

```
var data = {
    "controller":'Group',           // ws 控制器       
    "action":"getGroups",           // ws 方法名
    "content":{"token":token}       // 传的数据
};
var data = JSON.stringify(data);
ws.send(data);                      // 推送消息
```

1. 后端首先在EasySwooleEvent中进行onMessage事件处理函数的注册
```
static public function mainServerCreate(ServerManager $server, EventRegister $register): void
{
    // 添加 onMessage 的处理方式
    EventHelper::registerDefaultOnMessage($register, new WebSock());
}
```

2. 在 App/Sock/Parser/WebSock.php 中分析数据格式，分离出要调用的控制器名，方法名，以及数据
```
public function decode($raw, $client)
{
    $command = new CommandBean();
    $json = json_decode($raw,1);
    $controller_path = Config::getInstance()->getConf("App\WebsocketController\\");   // 定义 websocket 控制器存放目录
    $command->setControllerClass($controller_path.$json['controller']);               // 设置控制器名
    $command->setAction($json['action']);                                             // 设置方法名
    $command->setArg('content',$json['content']);                                     // 设置传递的参数
    return $command;
}
```

3. 定义好websocket控制器后即可进行代码逻辑的编写，以发送好友请求为例：
```
public function sendReq(){
    $content = $this->request()->getArg('content');
    $user = $this->getUserInfo();
    $to_number = $content['number'];
    
    // 不可添加自己好友
    ...

    // 查二者是否已经是好友
    ...

    // 准备发送请求的数据
    ...

    // 异步发送好友请求
    $fd = UserCacheService::getFdByNum($to_number);
    $taskData = [
        ...
    ];
    $taskClass = new Task($taskData);
    TaskManager::async($taskClass);
    
    // 用封装好的格式返回客户端已经发送
    $this->sendMsg(['data'=>'好友请求已发送！']);
}
```

## 部署

后端： clone 相关分支代码，在应用根目录下执行 php easyswoole start 即可，若要修改相关配置，则在 Config.php 中进行修改。
前端： clone git@github.com:LTalkTeam/LTalkHTML.git 修改 src/js/config.js 中的域名即端口号即可配置完成，，打开 login.html进行访问。

## 开源许可协议
LGPL许可协议，允许免费试用与二次开源。
