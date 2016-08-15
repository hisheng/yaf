# yaf
基于鸟哥的yaf，composer组建，建立自己的 框架


index.php 入口页面在 public/ 下面






一.windows下面，虚拟主机设置
1. apache2.4.9\conf\extra\httpd-vhosts.conf

<VirtualHost *:80>
    ServerAdmin webmaster@dummy-host2.example.com
    DocumentRoot "D:/yaf/public"
    ServerName yaf.zhs.com
    ErrorLog "logs/yof2.zhs.com-error.log"
    CustomLog "logs/yof2.zhs.com-access.log" common
</VirtualHost>

2.设置 windows系统 hosts
打开 C:\Windows\System32\drivers\etc\hosts

在最下面输入  127.0.0.1	yaf.zhs.com