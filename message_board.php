<?php

//全局配置，编译器设置，时区设置
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Asia/Shanghai');

//MySQL数据库设置：MySQL服务器地址、MySQL数据库名、MySQL管理员账户，MySQL管理员密码，MySQL主机端口
$cfg_db_server = '127.0.0.1';
$cfg_db_name = 'mbs';
$cfg_db_user = 'root';
$cfg_db_passwd = '';
$cfg_db_port = 3306;

?>

<html>
<head>
    <title>Web微型留言板</title>
    <!--层叠样式表-->
    <style type="text/css">
        h1 {
            font-family: 新宋体;
            color: khaki;
        }

        body {
            font-family: 新宋体;
            font-size: small;
            background-color: black;
            color: white;
            width: 600px;
        }

        a.warning {
            font-family: 新宋体;
            font-size: small;
            color: red;
        }

        a.id {
            font-family: 新宋体;
            font-size: small;
            color: lime;
        }

        a.user {
            font-family: 新宋体;
            font-size: small;
            color: orange;
        }

        a.time {
            font-family: 新宋体;
            font-size: small;
            color: orangered;
        }

        div.message {
            background-color: darkslategrey;
            border-style: outset;
        }

        a {
            font-family: 新宋体;
            font-size: medium;
            color: lime;
            text-underline: false;
        }

        textarea {
            background-color: darkslategrey;
            color: white;
        }

        div {
            margin: 6px;
            border: 2px;
        }
    </style>
</head>
<body>
<div>
<h1>Web微型留言板</h1>
</div>
<br/>

<?php
//Session会话开始
session_start();

//建立MySQL数据库连接
$con = mysqli_connect($cfg_db_server, $cfg_db_user, $cfg_db_passwd);
//判断MySQL连接成功否
if (!$con)
{
    die('<script>alert("无法连接至数据库服务器");</script>');

}
//选择数据库
mysqli_select_db($con, $cfg_db_name);


//判断用户操作：注册、登录、注销、提交
switch($_POST['opt'])
{
    case '提交留言':
        //取留言文本的base64编码
        $text = base64_encode($_POST['text']);

        if(!$_SESSION['user'])
        {
            echo '<a class="warning">用户尚未登录</a>';
            break;
        }

        if(!$_POST['text'])
        {
            echo '<a class="warning">留言内容为空</a>';
            break;
        }

        //通过最大，计算ID字段的值
        $sql = "SELECT ID FROM Messages ORDER BY ID DESC;";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_array($result);
        $id = $row['ID'] + 1;

        //插入新的留言记录
        $sql = "INSERT INTO Messages (ID, User, Time, Text) VALUES ('$id', '" . $_SESSION['user'] . "', '" . date('Y-m-d H:i:s') . "', '$text');";
        if(!mysqli_query($con, $sql))
        {
            switch(mysqli_errno($con))
            {
                default: die(mysqli_error($con)); break;
            }
        }

        //刷新页面
        header('location: '.$_SERVER['HTTP_REFERER']);
        break;

    case '删除留言':

        if(!$_SESSION['user'])
        {
            echo '<a class="warning">用户尚未登录</a>';
            break;
        }

        $id = $_POST['id'];
        $sql = "DELETE FROM Messages WHERE ID = '$id'";

        if(!mysqli_query($con, $sql))
        {
            switch(mysqli_errno($con))
            {
                default: die(mysqli_error($con)); break;
            }
        }

        //刷新页面
        header('location: '.$_SERVER['HTTP_REFERER']);
        break;
    case '登录':
        $user = $_POST['user'];
        $passwd = sha1($_POST['passwd']);

        if($_SESSION['user'])
        {
            echo "<a class=\"warning\">已登录用户: " . $_SESSION['user'] . "</a>";
            break;
        }

        if(!$_POST['user'])
        {
            echo '<a class="warning">用户名为空</a>';
            break;
        }

        if(!$_POST['passwd'])
        {
            echo '<a class="warning">密码为空</a>';
            break;
        }

        //取select结果集，并判断用户名、密码是否正确
        $sql = "SELECT * From Users;";

        $result = mysqli_query($con, $sql);

        while($row = mysqli_fetch_array($result))
        {
            if($row['User'] == $user && $row['Passwd'] == $passwd)
            {
                $_SESSION['user'] = $user;
                break;
            }
        }

        //Session字段未创建，表示用户名或密码错误
        if(!$_SESSION['user'])
        {
            echo '<a class="warning">登录失败，用户名或密码错误</a>';
            break;
        }

        //刷新页面
        header('location: '.$_SERVER['HTTP_REFERER']);
        break;

    case '退出当前用户':
        //判断Session字段是否存在，存在则销毁（退出登录），不存在则报错
        if($user = $_SESSION['user'])
        {
            unset($_SESSION['user']);
            echo "用户 $user 已注销";
        }
        else
        {
            echo "<a class=\"warning\">用户尚未登录</a>";
            break;
        }

        //刷新页面
        header('location: '.$_SERVER['HTTP_REFERER']);
        break;

    case '注册':
        $user = $_POST['user'];
        $passwd = sha1($_POST['passwd']);

        if($_SESSION['user'])
        {
            echo "<a class=\"warning\">已登录用户: " . $_SESSION['user'] . "</a>";
            break;
        }

        if(!$_POST['user'])
        {
            echo '<a class="warning">用户名为空</a>';
            break;
        }

        if(!$_POST['passwd'])
        {
            echo '<a class="warning">密码为空</a>';
            break;
        }

        //SQL插入新注册用户的记录
        $sql = "INSERT INTO Users (User, Passwd) VALUES ('$user', '$passwd');";

        if(!mysqli_query($con, $sql))
        {
            switch(mysqli_errno($con))
            {
                case 1062: die("用户名 $user 已被注册"); break;
                default: die(mysqli_error($con)); break;
            }
        }

        //提示注册成功
        echo '<script>alert("用户 ' . $user . ' 注册成功！");</script>';
        break;
}

//判定当前有没有登录，有则显示用户名并且显示退出登录按钮，否则显示注册或者登录按钮
if($user = $_SESSION['user'])
{
    echo "<div><a>当前用户名: </a><a class=\"user\">$user</a><br/><br/>" .
        '<form action="index.php" method="post">
        <input type="submit" name="opt" value="退出当前用户">
    </form></div>';
}
else
{
    echo
    '<div><form action="index.php" method="post">
        用户名: <input type="text" name="user">
        密码: <input type="password" name="passwd">
        <br/>
        <br/>
        <input type="submit" name="opt" value="登录">
        <input type="submit" name="opt" value="注册">
    </form></div>';
}

?>

<!--提交留言HTML代码段-->
<div>
<form action="index.php" method="post">
    <input type="hidden" name="opt" value="message">
    <textarea name="text" rows="8" cols="80" autofocus="true" required="true"></textarea>
    <br/>
    <input type="submit" name="opt" value="提交留言">
    <input type="button" value="刷新页面" onclick="location.reload();">
</form>
</div>

<div>
<?php

//显示留言板内容
$sql = "SELECT ID, User, Time, Text FROM Messages ORDER BY ID DESC;";

$result = mysqli_query($con, $sql);

while($row = mysqli_fetch_array($result))
{
    echo "<div class=\"message\"><form action=\"index.php\" method=\"post\"><input type=\"hidden\" name=\"id\" value=\"" . $row['ID'] . "\"><div style=\"float: right;\"><a class=\"id\">#" . $row['ID'] . "</a></div>";
    echo "<div style=\"float: left;\">用户 <a class=\"user\">" . $row['User'] . "</a> 于 <a class=\"time\">" . $row['Time'] . "</a></div><br/><br/><div style=\"float: bottom;\">";
    echo base64_decode($row['Text']) . "</div><br/>";
    //如果是当前登录用户，则显示删除按钮
    if($row['User'] == $_SESSION['user'])
    {
        echo '<div style="float: bottom;"><input type="submit" name="opt" value="删除留言"></div>';
    }
    echo '</form></div>';
}

//关闭MySQL数据库连接
mysqli_close($con);
?>
</div>
</body>
</html>