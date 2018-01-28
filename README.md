### 轻量级Web留言板（Lightweight Web MessageBoard）

- 基于 PHP + MySQL 环境
- MySQL表结构:

* messages 表:
```SQL
  CREATE TABLE `messages` (
  `ID` int(11) NOT NULL,
  `User` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Time` datetime NOT NULL,
  `Text` mediumtext COLLATE utf8_unicode_ci NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

* users 表:
```SQL
  CREATE TABLE `users` (
  `User` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Passwd` varchar(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

* 设置主键:
```SQL
  ALTER TABLE `messages` ADD PRIMARY KEY (`ID`);
  ALTER TABLE `users` ADD PRIMARY KEY (`User`);
```
