# Slim MNB Exchange Rate

Exchange rates from [mnb.hu](https://mnb.hu/).

### Why does this exist?

1. MNB only provides a SOAP interface.
2. Go, and many other technology stacks, does not come with good SOAP support.

### How does it work?

Using [MNB - Exchange Rate library](https://github.com/icetee/mnb-exchange-rate) and providing a simple
[Slim framework endpoint](https://www.slimframework.com/) wrapper around it.

### How can I run this?

1. Create a simple `.env` file containing a line with an `MNB_PASSWORD` variable
2. Start up the docker images with `docker-compose up -d`

### Examples

Using curl to retrieve the current EUR-HUF rates
```
> curl http://127.0.0.1:8023\?p\=abcd
2019-05-21	EUR	1	327.19
```

Using curl to retrieve given exchange rates
```
> curl http://127.0.0.1:8023\?p\=abcd\&startDate\=2019-05-01\&endDate\=2019-05-05\&currencies\=EUR,USD 
2019-05-03	EUR	1	323.82
2019-05-03	USD	1	290.03
2019-05-02	EUR	1	324.32
2019-05-02	USD	1	289.31
```

Using curl to retrieve given exchange rates with semicolon as separator
```
> curl http://127.0.0.1:8023\?p\=abcd\&delimeter\=\;\&startDate\=2019-05-01\&endDate\=2019-05-05\&currencies\=EUR,USD
2019-05-03;EUR;1;323.82
2019-05-03;USD;1;290.03
2019-05-02;EUR;1;324.32
2019-05-02;USD;1;289.31
```