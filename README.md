# Ltalk

- 在线预览：http://118.24.77.25/LTalkHTML/login.html

- 前端项目地址：https://github.com/LTalkTeam/LTalkHTML

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
- 聊天页面需要执行 init() （/App/WebsocketController/OnOpen.php）进行缓存的初始化，保存用户信息，token，以及 fd 的绑定，向所有好友推送上线消息
- 聊天，加好友，处理好友请求等操作，都以固定格式，推送消息到服务器，服务器转到相应 websocket 控制器去处理，返回结果
- 当用户关闭页面触发 onclose ，对缓存进行销毁

## 实现原理

本项目可分为两大类，登录与注册用 Http 基础应用实现，主聊天页面的所有功能都通过 easyswoole 的 WebSocket 应用模块来实现。由于
EasySwoole 专为api而生，本项目不论是基于 http 还是websocket的应用功能，都是采用 api 接口形式请求与返回。

### Http 基础应用的关键代码分析

以登录模块为例
```
// HttpController/Api/Login.php

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

### 后端

- git clone -b v1.0 git@github.com:octopuszy/LTalk-swoole.git
- 执行 composer update
- 创建数据库LTalk（或其他名，注意与配置文件保持一致），将 LTalk.sql 文件导入数据库
- 修改 Config.php
```
'MAIN_SERVER'=>[
    'HOST'=>'0.0.0.0',              
    'PORT'=>9502,                   // 修改端口号   
    'SETTING'=>[
        'task_worker_num' => 2,     // 异步任务进程数，通常与worker_num保持一致
        'task_max_request'=>10,
        'max_request'=>2500,        
        'worker_num'=>2             
    ],
],

'MYSQL' =>[                         // 配置 mysql，改为自己的主机名，用户名以及密码
    'HOST'=>'127.0.0.1',
    'USER'=>'root',
    'PASSWORD'=>'xxx',
    'DB_NAME'=>'LTalk'
],                     
'REDIS' =>[                         
    'host' => '127.0.0.1',          // redis主机地址
    'port' => 6379,                 // 端口
    'serialize' => false,           // 是否序列化php变量
    'auth' => null,                 // 密码
    'pool' => [
        'min' => 5,                 // 连接池最小连接数
        'max' => 100                // 连接池最大连接数
    ],
],                     
'database' =>[                      // 本项目用到 think-orm 所以需要在这里进行mysql相关配置
    'hostname' => '127.0.0.1',
    'database' => 'LTalk',
    'username' => 'root',
    'password' => 'xxx',
    'hostport' => '3306',
]
```
- 在应用根目录下执行 php easyswoole start 即可启动，等待客户端连接（php easyswoole start --d 可以以守护进程方式启动）
- php easyswoole stop 关闭服务器


### 前端

- clone git@github.com:LTalkTeam/LTalkHTML.git
- 修改 src/js/config.js，将 var ajaxUrl = 'http://118.24.77.25:9502'; 设置为自己的域名及其端口号
- 打开 login.html进行注册与访问


## 开源许可协议
apache许可协议，允许免费试用与二次开源。
