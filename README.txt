授权协议GPLv3，请参见：LICENSE.TXT 

DatabaseDesign文件夹为导出后的数据库。

部署步骤：
1.用DatabaseDesign中的CreateTable.sql创建数据库，并导入数据，其中autocatalogue_FetchConfig.sql为必选，用户表必须有一个ID为1的用户，建议全部导入。
2.修改include/systemConfig.php对应的数据库连接用户名及相关配置如SYSBASEPATH。
3.解压缩有代码。

演示地址：http://acm.nenu.edu.cn/winguse/AutoCatalogue/
我的博客：http://winguse.com/blog/2012/08/24/autocatalogue%E5%BC%80%E6%BA%90/

因为是个过去的小项目了，不能保证有修补什么的，而且后端php也实在是写得有点烂，这个项目对我最大的成长点就是JavaScript和基本的设计了。至少，有个拿得出手的JavaScript作品，例如：js/autoCatalgoue.js。

要是能对你产生帮助，我真的很高兴，谢谢！

请保留署名。

作者：程颖宇
2012-8-24