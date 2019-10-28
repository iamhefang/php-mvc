# 更新日志 | CHANGELOG

`U`(update): 修改功能的实现方式, 但和上一版本还兼容  
`C`(change): 修改功能的实现方式, 和上一版本不能直接兼容  
`A`(add): 新增功能, 类或方法  
`D`(delete): 删除功能或类  
`F`(fix): 修复bug


## 1.1.0

1. `C` `BaseController`中部分视图相关方法移到trait中
1. `A` TOKEN 授权方式可生效
1. `A` 可自定义分页和排序的参数键值
1. `A` `IApplication`接口添加`getLoginByToken(string $token)`方法
1. `A` 可自定义表前缀