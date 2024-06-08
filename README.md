# php示例
使用redis保存、读取数据，通过接口返回前端


## 安装
首先，你需要安装 Predis 和 Slim 框架，你可以通过composer来进行安装：
```
   composer require predis/predis
   composer require slim/slim "^3.0"
 ```

## 运行
```
 php -S localhost:8080
```

## 流程

后端有2个接口，/save接口保存数据，/lists接口把redis数据读取出来，

返回给前端，展示到列表当中。