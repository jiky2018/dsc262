##api/wx/user/login  用户登录接口

####链接
     http://domain/mobile/public/api/wx/user/login

####参数
1.   以下参数由  微信获取
2. code:qweqeqe     用户唯一标识  据此产生用户
3. nickname:名
4. gender:1
5. city:1
6. country:20
7. province:50
8. language:chinese
9. avatarurl:1.jpg


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data : 数据 （数组）
3. token   eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1aWQiOjcwLCJleHAiOjE1MDEzNzg2NjQsInBsYXRmb3JtIjoid3gifQ.-DzMqvPcB56YO8WD8KK98cWSCHwI08veU3sFw3kcxoM   本地存储  用于验证用户
4. openid  微信openid

