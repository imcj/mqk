日志处理
========

开发应用程序的时候，程序的执行日志数据是一个很重要的信息。在线上运行的程序出现异常的时候，我们需要很日志信息了解应用程序的状态。

StreamHandler默认将日志输出到控制台。

```yaml
logging:
  handlers:
    - StreamHandler
```

`$logger->debug`输出debug级别的日志信息。

```
$logger = LoggerFactory::shared()->getLogger(__CLASS__);
$logger->debug("Hello");
```

GelfHandler
-------------

在真实的生产环境中，我们不可能到服务器上去查看日志信息，虽然Monolog支持将日志输出到文件中。通常我们将日志输出一些分布式的集中
的日志服务器中，这里以Graylog距离。

```yaml
logging:
  handlers:
    - GelfHandler: {host: 127.0.0.1, port: 50002}
```

更多的Handler可以查看Monolog的文档。