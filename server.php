<?php
error_reporting(E_ALL);
ob_implicit_flush();
 
$sk=new Sock('127.0.0.1',8888);
$sk->run();
class Sock{
    public $sockets;//用以存储所有套接字描述符的数组，包括监听套接字和所有连接套接字，以作为select函数的参数
    public $users;//users是一个多维数组
    public $master;//监听套接字
    public $n;
    public $slen;
    public $sjen;
    public $sda;//已经接收到的报文段收集到的数据
    public $ar;
     
    public function __construct($address, $port){
        $this->master=$this->WebSocket($address, $port);
        $this->sockets=array('s'=>$this->master);
    }
     
     
    function run(){
        while(true){
            $changes=$this->sockets;
            $write=NULL; $except=NULL; 
            socket_select($changes,$write,$except,NULL);
            //socket_select($changes,$write=NULL,$except=NULL,NULL);
            foreach($changes as $sock){
                if($sock==$this->master){//判断是否是监听套接字监听到新的连接
                    $client=socket_accept($this->master);
                    //$key=uniqid();
                    $this->sockets[]=$client;//向套接字数组中添加数组元素
                    $this->users[]=array(//将新的客户端加入users
                        'socket'=>$client,
                        'shou'=>false
                    );
                }
                else{//不是监听套接字，说明是连接套接字接收到报文，可能是新建立的套接字的websocket握手报文也可能是老套接字收到消息
                    //$len=@socket_recv($sock,$buffer,2048,0);
                    $len = 0;
                    $buffer="";
                    do{
                        $l=socket_recv($sock,$buf,1000,0);
                        $len+=$l;
                        $buffer.=$buf;
                    }while($l==1000);
                    $k=$this->search($sock);//k是该套接字连接的客户端对应的编号
                    //if($len<7){????
                    if($len == 0){  //如果和客户端连接断开，则将该客户从users中剔除
                        $name=$this->users[$k]['name'];
                        $this->close($sock);
                       // $this->send2($name,$k);   待处理
                        continue;
                    }
                    else{
                        if(!$this->users[$k]['shou']){//新的连接套接字还未握手的话先握手完成websocket连接
                            $this->woshou($k,$buffer);
                        }else{//已经建立了websocket连接的连接套接字接收到消息报文
                            $buffer = $this->uncode($buffer,$k);
                            $this->send($k,$buffer);//根据buffer内容进行send
                        }
                    }
                }
            }
             
        }
         
    }
     
    function close($sock){
        $k=array_search($sock, $this->sockets);
        socket_close($sock);
        unset($this->sockets[$k]);
        unset($this->users[$k]);
        $this->e("key:$k close");
    }
     
    function search($sock){//返回的是客户端的编号（e.g 0,1,2,3.。。）
        foreach ($this->users as $k=>$v){
            if($sock==$v['socket']){
                return $k;
            }
        }
        return false;
    }
     
    function WebSocket($address,$port){//返回套接字描述符
        $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($server, $address, $port);
        socket_listen($server);
       // $this->e('Server Started : '.date('Y-m-d H:i:s'));
       // $this->e('Listening on   : '.$address.' port '.$port);
        echo 'Server Started : '.date('Y-m-d H:i:s')."\n";
        echo 'Listening on   : '.$address.' port '.$port."\n";
        return $server;
    }
     
     
    function woshou($k,$buffer){
        $buf  = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);
        $key  = trim(substr($buf,0,strpos($buf,"\r\n")));
     
        $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
         
        $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
        $new_message .= "Upgrade: websocket\r\n";
        $new_message .= "Sec-WebSocket-Version: 13\r\n";
        $new_message .= "Connection: Upgrade\r\n";
        $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
         
        socket_write($this->users[$k]['socket'],$new_message,strlen($new_message));
        $this->users[$k]['shou']=true;
        return true;
         
    }
     
/* function uncode($str){
        $mask = array();  
        $data = '';  
        $msg = unpack('H*',$str);  
        $head = substr($msg[1],0,2);  
        if (hexdec($head{1}) === 8) {  //head{1} = 8 即opcode为1000,即操作码表示关闭
            $data = false;  
        }else if (hexdec($head{1}) === 1){  //即消息数据类型为文本
            $mask[] = hexdec(substr($msg[1],4,2));  //每次取一个字节，第3个字节
            $mask[] = hexdec(substr($msg[1],6,2));  //第四个字节
            $mask[] = hexdec(substr($msg[1],8,2));  //第五个字节
            $mask[] = hexdec(substr($msg[1],10,2));  //第六个字节
           //总共4个字节的掩码
            $s = 12;  
            $e = strlen($msg[1])-2;  //
            //echo strlen($msg[1])." ".$e."\n";
           //echo $msg[1]." ".substr($msg[1],0,12)." ".strlen(substr($msg[1],0,12))."\n";
            $n = 0;  
            for ($i=$s; $i<= $e; $i+= 2) { //每次处理一个字节 
                $data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));//chr将ascii码转为对应字符  
                $n++;  
            }  
        }  
        return $data;
    }*/
    function uncode($str,$key){
        $mask = array();
        $data = '';
        $msg = unpack('H*',$str);
        $head = substr($msg[1],0,2);
        if ($head == '81' && !isset($this->slen[$key])) {
            $len=substr($msg[1],2,2);
            $len=hexdec($len);
            if(substr($msg[1],2,2)=='fe'){//126 表示 payload length bits扩展2个字节
                $len=substr($msg[1],4,4);
                $len=hexdec($len);
                $msg[1]=substr($msg[1],4);
            }
            else if(substr($msg[1],2,2)=='ff'){//127 表示payload length bits扩展8个字节
                $len=substr($msg[1],4,16);
                $len=hexdec($len);
                $msg[1]=substr($msg[1],16);
            }
            $mask[] = hexdec(substr($msg[1],4,2));
            $mask[] = hexdec(substr($msg[1],6,2));
            $mask[] = hexdec(substr($msg[1],8,2));
            $mask[] = hexdec(substr($msg[1],10,2));
            $s = 12;//2 + 2 + 4*2
            $n=0;
        }
        else if($this->slen[$key] > 0){
            $len=$this->slen[$key];
            $mask=$this->ar[$key];
            $n=$this->n[$key];
            $s = 0;
        }
    
        $e = strlen($msg[1])-2;
        for ($i=$s; $i<= $e; $i+= 2) {
            $data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));
            $n++;
        }
        $dlen=strlen($data);
    
        if($len > 255 && $len > $dlen+intval($this->sjen[$key])){
            $this->ar[$key]=$mask;
            $this->slen[$key]=$len;
            $this->sjen[$key]=$dlen+intval($this->sjen[$key]);
            $this->sda[$key]=$this->sda[$key].$data;
            $this->n[$key]=$n;
            return false;
        }
        else{
            unset($this->ar[$key],$this->slen[$key],$this->sjen[$key],$this->n[$key]);
            $data=$this->sda[$key].$data;
            unset($this->sda[$key]);
            return $data;
        }
    
    }
    /*function uncode($data){
        $bytes = $data;
        $data_length = "";
        $mask = "";
        $coded_data = "" ;
        $decoded_data = "";
        $data_length = $bytes[1] & 127;
        if($data_length === 126){
            $mask = substr($bytes, 4, 8);
            $coded_data = substr($bytes, 8);
        }else if($data_length === 127){
            $mask = substr($bytes, 10, 14);
            $coded_data = substr($bytes, 14);
        }else{
            $mask = substr($bytes, 2, 6);
            $coded_data = substr($bytes, 6);
        }
        for($i=0;$i<strlen($coded_data);$i++){
            $decoded_data .= $coded_data[$i] ^ $mask[$i%4];
        }
        //echo "Server Received->".$decoded_data."\r\n";
        return $decoded_data;
    }*/
     

    function code($msg){
		$frame = array();  
		$frame[0] = '81';  
		$len = strlen($msg);
		if($len < 126){
			$frame[1] = $len<16?'0'.dechex($len):dechex($len);
		}else if($len < 65025){
			$s=dechex($len);
			$frame[1]='7e'.str_repeat('0',4-strlen($s)).$s;
		}else{
			$s=dechex($len);
			$frame[1]='7f'.str_repeat('0',16-strlen($s)).$s;
		}
		$frame[2] = $this->ord_hex($msg);  
		$data = implode('',$frame);  
		return pack("H*", $data);  
	}
     
    function ord_hex($data)  {  
        $msg = '';  
        $l = strlen($data);  
        for ($i= 0; $i<$l; $i++) {  
            $msg .= dechex(ord($data{$i}));  
        }  
        return $msg;  
    }
     
    function send($k,$msg){//这个msg是uncode之后返回的结果，所以可以认为解析websocket报文的过程对于我来说事透明的，k标记消息来源，msg是消息内容
        /*$this->send1($k,$this->code($msg),'all');*/
        parse_str($msg,$g);//将字符串解析到数组中
        $this->e($msg);
        $ar=array();
        if($g['type']=='add'){
            //echo "add.\n";
            $this->users[$k]['name']=$g['name'];
            $this->users[$k]['code']=$k;/////////////////////////////////////////////////////11-21-15, this is quite critical
           
            $ar['name']=$g['name'];
            $ar['isadmin']=0;
            $ar['time']=date("Y-m-d H:i:s");
            $ar['code']=$k;
           // print_r($this->users[0]);
            //$ar['users']=$this->users;//这样失败的原因是因为套接字描述符中存在‘#’符号，JSON编码有问题。。。。。。。。。。。
            //print_r($this->users);
           // echo "json:".json_encode($this->users)."\n";
            $ar['users']=$this->getusers();////////////////////////////////////////////////////////////////////////////////////////////
         
            
            $key='all';
           
            $ar['type']= 'madd';
            $msg=json_encode($ar);
            //echo "msg: ".$msg;
            $this->e($msg);
            $msg = $this->code($msg);
            
            socket_write($this->users[$k]['socket'],$msg,strlen($msg));
            
            $ar['type']= 'add';
            $ar['users'] = false;
            $msg=json_encode($ar);
            //echo "msg: ".$msg;
            $this->e($msg);
            $msg = $this->code($msg);
            foreach($this->users as $v){
                if($this->users[$k]['socket'] != $v['socket']){
                    socket_write($v['socket'],$msg,strlen($msg));
                }
            }
        }
        else if($g['type']=='mes'){//处理聊天消息
            $ar['type'] = 'mes';
            $ar['nrong']=$g['nr'];
            $ar['time']=date("Y-m-d H:i:s");
            $ar['code']=$k;//发送方
            $ar['isadmin']=0;
            $key=$g['key'];
            $ar['code1']=$key;//接收方
            
            
            $msg=json_encode($ar);
            //echo "msg: ".$msg;
            $this->e($msg);
            $msg = $this->code($msg);
            
            
            if($key=='all'){
                foreach($this->users as $v){
                    socket_write($v['socket'],$msg,strlen($msg));
                }
            }else{
                if($k!=$key)//不能发送给自己
                    socket_write($this->users[$k]['socket'],$msg,strlen($msg));//给自己发一条先
                socket_write($this->users[$key]['socket'],$msg,strlen($msg));//发送给指定用户
            }
        }
        else if($g['type'] == 'rmove'){
            $ar['type'] = 'rmove';
            $ar['code'] = $g['key'];
            $this->users[$g['key']]['name'] = null;
            print_r($this->users);
            $ar['time'] = date("Y-m-d H:i:s");
            $ar['msg'] = false;
            
            $msg=json_encode($ar);
            $this->e($msg);
            $msg = $this->code($msg);
            
            foreach($this->users as $v){
                socket_write($v['socket'],$msg,strlen($msg));
            }
          
        }
        else if($g['type'] == 'pic'){
            $ar['type'] = 'mes';
            $ar['nrong']=$g['nr'];
            $ar['time']=date("Y-m-d H:i:s");
            $ar['code']=$k;//发送方
            $ar['isadmin']=0;
            $key=$g['key'];
            $ar['code1']=$key;//接收方
            
            
            $msg=json_encode($ar);
            //echo "msg: ".$msg;
            $this->e($msg);
            $msg = $this->code($msg);
            
            
           
            if($k!=$key)//不能发送给自己
                socket_write($this->users[$k]['socket'],$msg,strlen($msg));//给自己发一条先
            socket_write($this->users[$key]['socket'],$msg,strlen($msg));//发送给指定用户
        }
        else{
            //do nothing
        }
        /*$msg=json_encode($ar);
        //echo "msg: ".$msg;
        $this->e($msg);
        $msg = $this->code($msg);
        $this->send1($k,$msg,$key);//将这个消息转发给浏览器*/
        //socket_write($this->users[$k]['socket'],$msg,strlen($msg));
    }
     
    function getusers(){//返回所有用户的昵称
        $ar=array();
        foreach($this->users as $k=>$v){
          // if(isset($this->users[$k])){
               $ar[$k]['name']=$v['name'];
               $ar[$k]['code']=$v['code'];
           //}
        }
        return $ar;
    }
     
   /* function send1($k,$str,$key='all'){//向客户端发送多种类型的消息
        if($key=='all'){
            foreach($this->users as $v){
                    socket_write($v['socket'],$str,strlen($str));
            }
        }else{
            if($k!=$key)//不能发送给自己
            socket_write($this->users[$k]['socket'],$str,strlen($str));
            socket_write($this->users[$key]['socket'],$str,strlen($str));
        }
    }*/
     
  /*  function send2($name,$k){
        $ar['remove']=true;
        $ar['removekey']=$k;
        $ar['nrong']=$name.'退出聊天室';
        $str = $this->code(json_encode($ar));
       // $this->send1(false,$str,'all');
        foreach($this->users as $v){
            socket_write($v['socket'],$str,strlen($str));
        }
    }*/
     
    function e($str){//按指定格式输出
        $path=dirname(__FILE__).'/log.txt';
        $str=$str."\n";
        error_log($str,3,$path);
        echo iconv('utf-8','gbk//IGNORE',$str);
    }
}
?>