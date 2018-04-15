# Ltalk
### 本项目v1版本有以下功能：
- [x] 登录 
- [x] 注册
- [ ] 添加/删除好友
- [ ] 好友即时聊天
- [ ] 添加/退出群组
- [ ] 群组及时聊天
- [ ] 世界聊天模块（所有在线人员）
- [ ] 好友/群组离线上线提醒

### v2 或以后版本会完善
- 离线接受好友请求
- 存储聊天记录
- 查看历史聊天记录

## 标准与规范
1. 本项目采用api接口形式，接收与返回数据
2. 尽可能遵循 restful 规范标准
3. 前后端分离
4. 自定义全局异常处理类与验证层，保证输入输出的规范性

## 目录结构

目录结构如下：

~~~
LTalk  WEB部署目录
├─app          				应用目录
│  ├─Exception             	自定义异常
│  ├─HttpController        	HttpApi 控制器目录
│  │  ├─Common.php      	公共方法
│  │  └─Router.php      	自定义路由
│  │
│  ├─Model        			tp orm 
│  ├─Service         		服务层
│  ├─Sock           		websocket 输入输出规范配置
│  ├─Task           		异步Task方法模块
│  ├─Utility           		进程池
│  ├─Validate           	自定义验证层模块
│  └─WebsocketController    Websocket Api 控制器目录
...
(其他为 easyswoole 框架自带)
~~~
