# RESTful

[TOC]

## 规范

## HTTP动作
- `get` 
- `post`
- `put`
- `delete`

## 鉴权
- time
- token

## 数据库的三大范式
>往后的每一个范式都要满足其前的范式要
1. 第一范式：每一列都是不可分割的原子数据项
2. 第二范式：实体的属性完全依赖于主关键字
3. 第三范式：任何的非主属性不依赖于其他的非主属性

## 设计数据库的技巧
- 一个对象一张表

- 一张表一个主键

- 表名中有数据库名作前缀

- 字段名中有表名作前缀

- 增加前缀后尽量使用缩写

- 数据表关系处理

## 接口文档的规范
>实例

|参数|必选|类型|说明|
|:-:|:-:|:-:|:-:|
|tiem|true|int|时间戳|
|token|true|string|用于确认访问者的身份|
|usename|true|string|只接受`手机号`|
|password|true|string|用户密码|

>返回说明
```js
"data": {
	"user_id": "27",//用户id
	"user_tag": "1"//用户身份
},
"msg": ""//401:用户名不存在/402:手机号不存在/403:密码不正确
```

## 接口安全

+ 接口被恶意调用
>增加时间戳字段

+ 接口数据被篡改
>增加`token`字段

+敏感信息被截获 
>用户密码永不返回

## 准备工作
1. 配置路由
2. 参数未过滤之前，不运行具体的接口
3. 统一进行参数过滤

## 验证码接口
>`get` api.tp.com/code/:time/:token/:username/:is_exist
|参数|类型|必选可选|默认|描述|
|:-:|:-:|:-:|:-:|:-:|
|time|int|必选|无|当前时间的时间戳,避免恶意调用|
|token|string|必选|无|token验证字段，避免接口数据被篡改|
|username|string|必选|无|用户名，可以是邮箱或者手机号|
|is_exist|int|必选|无|指定在数据库中是否存在|

```json
{
	"code": 200,
	"msg": "邮件发送成功，请注意查收",
	"data": []
}
```
## 注册接口
>`post`api.tp.com/user/register
|参数|类型|必选或可选|默认值|描述|
|:-:|:-:|:-:|:-:|:-:|
|user_name|string|必选|无|用户注册的用户名，可以是邮箱或手机号|
|user_pwd|string|必选|无|注册的密码，32位的MD5哈希值|
|code|int|必选|无|调用验证码接口获得的验证码|
```json
{
	"code": 200,
	"msg": "用户注册成功!",
	"data": []
}
```
## 用户登陆接口
>`post`api.tp.com/user/login
|参数|类型|必选或可选|默认值|描述|
|:-:|:-:|:-:|:-:|:-:|
|time|int|必选|无|**时间戳**用于判断请求是否超时|
|token|string|必选|无|锁定来访者的身份|
|user_name|string|必选|无|手机号或邮箱|
|user_pwd|string|必选|无|MD5之后的用户密码|
```json
{
	"code": 200,
	"msg": "登陆成功",
	"data": {
		"user_id": 4,
        "user_name": "",
        "user_phone": "",
        "user_email": "2858695659@qq.com",
        "user_regtime": 1541377701
	}
}
```
## 用户上传头像
>`post`api.tp.com/user/avatar
|参数|类型|必选or可选|默认值|描述|
|:-:|:-:|:-:|:-:|:-:|
|time|int|必选|无|时间戳|
|token|string|必选|无|锁定来访者的身份|
|user_id|string|必选|无|用户ID|
|user_avatar|file|必选|无|用户头像(200*200)|
```json
{
    "code": 200,
    "msg": "上传头像成功",
    "data": "D:\\www\\tp5\\public\\uploads\\5be041a812294.png"
}
```
## 用户修改密码
>`post`api.tp.com/user/change_pwd
|参数|类型|必选or可选|默认值|描述|
|:-:|:-:|:-:|:-:|:-:|
|time|int|必选|无|时间戳|
|token|string|必选|无|锁定来访者身份，确认数据不被篡改|
|user_name|string|必选|无|用户手机或邮箱|
|user_ini_pwd|string|必选|无|用户原来的密码|
|user_pwd|string|必选|无|用户的新密码|
```json
{
    "code": 200,
    "msg": "修改密码成功",
    "data": []
}
```
## 用户找回密码
>`post`api.tp.com/user/reset_pwd
|参数|类型|必选or可选|默认值|描述|
|:-:|:-:|:-:|:-:|:-:|
|time|int|必选|无|时间戳|
|token|string|必选|无|token用于锁定来访者的身份|
|user_name|string|必选|无|用户手机号或邮箱|
|code|int|必选|无|验证码|
|user_pwd|string|必选|无|用户新密码|
```json
{
    "code": 400,
    "msg": "修改密码成功",
    "data": []
}
```
## 用户绑定邮箱
>`post`api.tp.com/user/bind_email
|参数|类型|必选or可选|默认值|描述|
|:-:|:-:|:-:|:-:|:-:|
|time|int|必选|无|时间戳|
|token|string|必选|无|锁定来访者身份的验证|
|user_id|string|必选|无|用户的id|
|email|string|必选|无|邮箱|
|code|int|必选|无|验证码|

## 用户设置昵称
>`post`api.tp.com/user/nickname
|参数|类型|必选or可选|默认值|描述|
|:-:|:-:|:-:|:-:|:-:|
|time|int|必选|无|时间戳|
|token|string|必选|无|锁定来访者的身份，防止数据被篡改|
|user_id|int|必选|无|用户id|
|user_nickname|string|必选|无|用户昵称|
```json
{
    "code": 200,
    "msg": "修改昵称成功",
    "data": []
}
```
## 新增文章接口
>`post`api.tp.com/aritcle
|参数|类型|必选or可选|默认值|描述|
|time|int|必选|无|时间戳|
|token|string|必选|无|锁定来访者的身份|
|article_uid|int|必选|无|文章用户id|
|article_title|string|必选|无|文章的标题|
```json
{
    "code": 200,
    "msg": "新增文章成功!",
    "data": "2"
}
```
## 文章列表接口
>`get`api.tp.com/articles
|参数|类型|必选or可选|默认值|描述|
|time|int|必选|无|时间戳|
|token|string|必选|无|token用于锁定来访者的身份|
|user_id|int|必选|无|用户id|
|num|int|可选|10|每页的总数|
|page|int|可选|1|页码|
```json
{
    "code": 200,
    "msg": "查询成功",
    "data": {
        "articles": [
            {
                "article_id": 2,
                "article_title": "titleOfArticleII",
                "article_ctime": 1541471490,
                "user_name": "xw_Von"
            },
            {
                "article_id": 1,
                "article_title": "titleOfArticleI",
                "article_ctime": 1541471435,
                "user_name": "xw_Von"
            }
        ],
        "page_total": 1
    }
}
```
## 查询单个文章信息
>`get`api.tp.com/article
|参数|类型|必选or可选|默认值|描述|
|:-:|:-:|:-:|:-:|:-:|
|time|int|必选|无|时间戳|
|token|string|必选|无|用于锁定来访者的身份|
|article_id|int|必选|无|文章id|
```json
{
    "code": 200,
    "msg": "查询成功",
    "data": {
        "article_id": 2,
        "article_title": "titleOfArticleII",
        "article_ctime": 1541471490,
        "article_content": "Content of Article II",
        "user_name": "xw_Von"
    }
}
```
## 修改/保存文章
>`post`api.tp.com/article
|参数|类型|必选or可选|默认值|描述|
|time|int|必选|无|时间戳|
|token|string|必选|无|用于锁定来访者的身份|
|article_id|int|必选|无|文章的id|

## 删除文章
>`delete`api.tp.com/article
|参数|类型|必选or可选|默认值|描述|
|:-:|:-:|:-:|:-:|:-:|
|time|int|必选|无|时间戳|
|token|string|必选|无|用于锁定来访者的身份|
|article_id|int|必选|无|文章id|