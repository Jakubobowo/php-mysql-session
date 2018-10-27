<?php
/*
Welcome

If you want to use you have to:

create table in mysql.


CREATE TABLE IF NOT EXISTS `jsessions` (
  `sid` tinytext,
  `sex` int(11) ,
  `sdata` text ,
  `sbrowser` text 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


put server name, user, password, base,

  $this->dbHandle = @new mysqli("servername", "user", "password", "base");

*/

$file =  basename($_SERVER['SCRIPT_NAME']);

class session {
 private $lifeTime;
 private $dbHandle;
 private $browser;
 public $newExp;

 public function __construct(){
  global $con;
  $this->lifeTime = 21600;
  $this->browser = $_SERVER['HTTP_USER_AGENT'];
  
  //set mysql base here
  $this->dbHandle = @new mysqli("servername", "user", "password", "base");
  
  if ($this->dbHandle){
    session_set_save_handler(array(&$this,"open"), array(&$this,"close"), array(&$this,"read"), array(&$this,"write"), array(&$this,"destroy"), array(&$this,"gc"));
    ini_set('session.gc_probability', 50);
    session_name("jsession");
    @session_start();
    $this->newExp = @time()+ $this->lifeTime;
  }
 }
 public function __destruct(){
  @session_write_close();
 }
 function open($savePath, $sessName){
  if ($this->dbHandle)
    return true;
  return false;
 }
 function close(){
  $this->gc($this->lifeTime);
  return @$this->dbHandle->close();
 }
 function read($sessID){
  $q = "select sdata from jsessions where sid='".@$this->dbHandle->real_escape_string($sessID)."' && sex>".time()
        ." && sbrowser='".@$this->dbHandle->real_escape_string($this->browser)."'";
  $r = @$this->dbHandle->query($q);
  if ($r)
   if (@$r->num_rows==1){
   	 $row = @$r->fetch_assoc();
	   return $row['sdata'];
   }
  return "";
 }
 function write($sessID,$sessData){
  $q1 = "select sex from jsessions where sid='".@$this->dbHandle->real_escape_string($sessID)
           ."' && sbrowser='".@$this->dbHandle->real_escape_string($this->browser)."'";
  $r1 = @$this->dbHandle->query($q1);
  if ($r1){
    if (@$r1->num_rows==1){
      $q2 = "update jsessions set sex=".@$this->dbHandle->real_escape_string($this->newExp)
            .", sdata='".@$this->dbHandle->real_escape_string($sessData)."' where sid='".@$this->dbHandle->real_escape_string($sessID)
           ."' && sbrowser='".@$this->dbHandle->real_escape_string($this->browser)."'";
      $r2 = @$this->dbHandle->query($q2);
      if ($r2)
        if (@$this->dbHandle->affected_rows==1)
          return true;
    }else{
      $q3 = "insert into jsessions (sid, sex, sdata, sbrowser) values('"
              .@$this->dbHandle->real_escape_string($sessID)."', ".@$this->dbHandle->real_escape_string($this->newExp).", '"
              .@$this->dbHandle->real_escape_string($sessData)."', '".@$this->dbHandle->real_escape_string($this->browser)."')";
      $r3 = @$this->dbHandle->query($q3);
      if ($r3)
        if (@$this->dbHandle->affected_rows==1)
          return true;
    }
  }
  return false;
 }
 function destroy($sessID){
  $q = "delete from jsessions where sid='".@$this->dbHandle->real_escape_string($sessID)."' && sbrowser='"
       .@$this->dbHandle->real_escape_string($this->browser)."'";

  $r = @$this->dbHandle->query($q);
  if ($r)
    if (@$this->dbHandle->affected_rows==1)
      return true;
  return false;
 }
 function gc($sessMaxLifeTime){
  $t = @time()-(60*60*24);
  $q = "delete from jsessions where sex<".$t;
  $r =  @$this->dbHandle->query($q);
  if ($r)
    return @$this->dbHandle->affected_rows;
  return false;
 }
}

new session;
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Session by Bober Jakub.</title>
<style>
html{
text-align: center;
}
</style>
</head>
<body>
<div style="margin: 40px;font-size: 18px;">
Ssessions<br>
<a href="<?php echo $file?>?add=-1">-1</a> <a style="margin-left: 20px;" href="<?php echo $file?>?add=1">+1</a>
</div>
<div style="margin: 30px; font-size: 24px;">
<?php
$s = '';
if (isset($_SESSION['operand'])){
    if (isset($_GET['add'])){
      $add = $_GET['add'];
      if ($add==1){
        $_SESSION['operand']++;         
      }
      if ($add==-1){
        $_SESSION['operand']--;
      }
      $s.= $_SESSION['operand'];
    }
} else {
     $_SESSION['operand'] = 0;
     $s .= 0; 
}
echo $s;
?>
<br><br><hr>
<p style="font-size:12px;">Session by <a href="http://jakubowo.net">Bober Jakub</a></p>
</div>
</body>
</html>
