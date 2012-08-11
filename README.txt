授权协议请参见：LICENSE.TXT 

DatabaseDesign文件夹为导出后的数据库。

部署步骤：
1.用DatabaseDesign中的CreateTable.sql创建数据库，并导入数据，其中autocatalogue_FetchConfig.sql为必选，用户表必须有一个ID为1的用户，建议全部导入。
2.修改include/systemConfig.php对应的数据库连接用户名及相关配置如SYSBASEPATH。
3.解压缩有代码。


请保留署名。

作者：程颖宇
2012-6-27