<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="utf-8">
    <meta name="author" content="̽����">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <title>php_HTML5_������</title>
    <style type="text/css">
        body,p{margin:0px; padding:0px; font-size:14px; color:#333; font-family:Arial, Helvetica, sans-serif;}
        var{font-style: normal;}
        #ltian,.rin{width:98%; margin:5px auto;}
        #ltian{border:1px #ccc solid;overflow-y:auto; overflow-x:hidden; position:relative;}
        #ct{margin-right:111px; height:100%;overflow-y:auto;overflow-x: hidden;}
        #us{width:110px; overflow-y:auto; overflow-x:hidden; float:right; border-left:1px #ccc solid; height:100%; background-color:#F1F1F1;}
        #us p{padding:3px 5px; color:#08C; line-height:20px; height:20px; cursor:pointer; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;}
        #us p:hover,#us p:active,#us p.ck{background-color:#069; color:#FFF;} /* ��ʾ���*/
        #us p.my:hover,#us p.my:active,#us p.my{color:#333;background-color:transparent;}/* ��ʾ�Լ����ǳ�*/
        button{float:right; width:80px; height:35px; font-size:18px;}
        input{width:100%; height:30px; padding:2px; line-height:20px; outline:none; border:solid 1px #CCC;}
        .rin p{margin-right:160px;}
        .rin span{float:right; padding:6px 5px 0px 5px; position:relative;}
        .rin span img{margin:0px 3px; cursor:pointer;}
        .rin span form{position:absolute; width:25px; height:25px; overflow:hidden; opacity:0; top:5px; right:5px;}
        .rin span input{width:180px; height:25px; margin-left:-160px; cursor:pointer}

        #ct p{padding:5px; line-height:20px;}
        #ct a{color:#069; cursor:pointer;}
        .admin1{color:#C00 !important;}
        #ct span{color:#999; margin-right:10px;}
        .c2{color:#999;}
        .c3{background-color:#DBE9EC; padding:5px;}
        .qp{position:absolute; font-size:12px; color:#666; top:5px; right:130px; text-decoration:none; color:#069;}
        #ems{position:absolute; z-index:5; display:none; top:0px; left:0px; max-width:230px; background-color:#F1F1F1; border:solid 1px #CCC; padding:5px;}
        #ems img{width:44px; height:44px; border:solid 1px #FFF; cursor:pointer;}
        #ems img:hover,#ems img:active{border-color:#A4B7E3;}
        #ems a{color:#069; border-radius:2px; display:inline-block; margin:2px 5px; padding:1px 8px; text-decoration:none; background-color:#D5DFFD;}
        #ems a:hover,#ems a:active,#ems a.ck{color:#FFF; background-color:#069;}
        .tc{text-align:center; margin-top:5px;}
    </style>
</head>

<body>
<div id="ltian">
    <div id="us" class="jb">
    <!-- �������������ʾ�û����б� -->
    </div>
    <div id="ct"><!-- ������ʾ��Ϣ������ --></div>
    <a href="javascript:;" class="qp" onClick="this.parentNode.children[1].innerHTML=''">����</a>
</div>
<div class="rin">
    <button id="sd">����</button>
    <span><img src="sk/t.png" title="����" id="imgbq"><img src="sk/e.png" title="�ϴ�ͼƬ"><form><input type="file" title="�ϴ�ͼƬ" id="upimg"></form></span>
    <p><input id="nrong"></p>
</div>
<div id="ems"><p></p><p class="tc"></p></div>
<script>
    if(typeof(WebSocket)=='undefined'){
        alert('����������֧�� WebSocket ���Ƽ�ʹ��Google Chrome ���� Mozilla Firefox');
    }
</script>
<script charset="gb2312" src="a.js" type="text/javascript"></script>
<script>
(function(){
    var key='all',mkey;//���key�Ͷ�Ӧusers��code�����
    var users={};
   // var url='ws://www.yxsss.com:8000';
    var url = 'ws://127.0.0.1:8888';
    var so=false,n=false;
    var lus=A.$('us'),lct=A.$('ct');//lus���û����б���
    var isadmin = 0;//??????????????
    function st(){//////////////////////////////////
		
        var sr=window.location.href.match(/name=([^&]+)/);
        if(sr && sr[1]){
            n=decodeURIComponent(sr[1]);
           
        }
        else{
            n=prompt('������������֣�');
        }
        n=n.substr(0,16);
        if(!n){
            return ;
        }
        so=new WebSocket(url);//
        so.onopen=function(){
            if(so.readyState==1){
                so.send('type=add&name='+n);
            }
        }

        so.onclose=function(){
        	alert("�����ж�!");
            so=false;
            lct.appendChild(A.$$('<p class="c2">�˳�������</p>'));
        }

        so.onmessage=function(msg){
        	//alert(msg.data);
            eval('var da='+msg.data);
           // alert(da.users[1]['name']);
            var obj=false,c=false;
            if(da.type== "add"){//���������û�����
                //alert("add");///////////////////////////////////////////////
                var obj=A.$$('<p>'+da.name+'</p>');//�������Ҳ��б���ʾ�û���
                lus.appendChild(obj);
                cuser(obj,da.code,0);
                obj=A.$$('<p><span>['+da.time+']</span>��ӭ<a class="admin'+da.isadmin+'">'+da.name+'</a>����</p>');//isadmin????
                //obj=A.$$('<p><span>['+da.time+']</span>��ӭ<a class="admin'+'">'+da.name+'</a>����</p>');
                c=da.code;
            }
            else if(da.type=='madd'){//�Լ�����
                mkey=da.code;//my key�Ļ�ȡ
               // alter(da.users);
                da.users.unshift({'code':'all','name':'���'});//���µĶ�Ԫ��(code,name):(all,���)����users��
				//alter(123);
                for(var i=0;i<da.users.length;i++){//�ӷ������˻�ȡ���е�users�б�
                   if(da.users[i].name != null){
                	   var obj=A.$$('<p>'+da.users[i].name+'</p>');
                       lus.appendChild(obj);
                       if(mkey!=da.users[i].code){
                           cuser(obj,da.users[i].code,da.isadmin);
                       }
                       else{
                           obj.className='my';
                           document.title=da.users[i].name;
                       }
                   }
                }
                isadmin = da.isadmin;
                obj=A.$$('<p><span>['+da.time+']</span>��ӭ'+da.name+'����</p>');
                users.all.className='ck';//
            }

            if(obj==false){//˵���������˼��룬�����������͵���Ϣ
                if(da.type=='rmove'){
                    if(da.msg){//
                        var _tms = da.msg;
                    }
                    else{
                        var _tms = users[da.code].innerHTML+'�˳�������';
                    }
                    var obj=A.$$('<p class="c2"><span>['+da.time+']</span>'+_tms+'</p>');
                    lct.appendChild(obj);
                    users[da.code].del();
                    delete users[da.code];//�����û����б���ɾ��
                }
                else if(da.type=='mes'){//������Ϣ�Ĵ���
                    da.nrong=da.nrong.replace(/{\\(\d+)}/g,function(a,b){
                        return '<img src="sk/'+b+'.gif">';
                    }).replace(/^data\:image\/png;base64\,.{50,}$/i,function(a){
                        return '<img src="'+a+'">';
                    });
                    //da.code ����Ϣ�˵�code
                    if(da.code1==mkey){//code1��Ƿ���Ŀ���code,��������շ�����
                        obj=A.$$('<p class="c3"><span>['+da.time+']</span><a>'+users[da.code].innerHTML+'</a>����˵��<var class="admin'+da.isadmin+'">'+da.nrong+'</var></p>');
                        c=da.code;
                    }
                    else if(da.code==mkey){//code��Ƿ���Դͷ��code,��������ͷ�����
                        if(da.code1!='all')
                            obj=A.$$('<p class="c3"><span>['+da.time+']</span>�Ҷ�<a>'+users[da.code1].innerHTML+'</a>˵��<var class="admin'+da.isadmin+'">'+da.nrong+'</var></p>');
                        else
                            obj=A.$$('<p><span>['+da.time+']</span>�Ҷ�<a>'+users[da.code1].innerHTML+'</a>˵��<var class="admin'+da.isadmin+'">'+da.nrong+'</var></p>');
                        c=da.code1;
                    }
                    else{
                   	 	obj=A.$$('<p><span>['+da.time+']</span><a>'+users[da.code].innerHTML+'</a>��'+users[da.code1].innerHTML+'˵��<var class="admin'+0+'">'+da.nrong+'</var></p>');
                     	c=da.code;
                    }
                    /*else if(da.code==false){//???
                        //obj=A.$$('<p><span>['+da.time+']</span><var class="admin'+da.isadmin+'">'+da.nrong+'</var></p>');
                    }
                    else if(da.code1){//???
                        obj=A.$$('<p><span>['+da.time+']</span><a>'+users[da.code].innerHTML+'</a>��'+users[da.code1].innerHTML+'˵��<var class="admin'+0+'">'+da.nrong+'</var></p>');
                        c=da.code;
                    }*/
                }
                else{

                }
            }
            if(c){//ʵ�ֵ����Ϣ������������������Ĺ���
                obj.children[1].onclick=function(){
                    users[c].onclick();
                }
            }
            lct.appendChild(obj);
            lct.scrollTop=Math.max(0,lct.scrollHeight-lct.offsetHeight);
        }
    }
    A.$('sd').onclick=function(){//���������Ϣ��Ĵ���
   	 
         if(!so){
            return st();
        }
        var da=A.$('nrong').value.trim();
        if(da==''){
            alert('���ݲ���Ϊ��');
            return false;
        }
        A.$('nrong').value='';
        so.send('type=mes'+'&nr='+esc(da)+'&key='+key);
    }
    A.$('nrong').onkeydown=function(e){
        var e=e||event;
        if(e.keyCode==13){
            A.$('sd').onclick();
        }
    }
    function esc(da){////??????
        da=da.replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\"/g,'&quot;');
        return encodeURIComponent(da);
    }
    function cuser(t,code,isa){//it's critical
        users[code]=t;//���ӷ�������get����users�б����뱾��
       // if(isa){
            t.title = '˫���޳����û�!';
        //}
        t.ondblclick=function(){//˫�����Ĳ���
            so.send('type=rmove&key='+code+'&name='+t.innerHTML);
        }
        t.onclick=function(){
            t.parentNode.children.rcss('ck','');
            t.rcss('','ck');
            key=code;//ʵ�ֶ�ĳһ���û�˵���Ĺ���
        }
    }
    A.$('ltian').style.height=(document.documentElement.clientHeight - 70)+'px';
    st();


    var bq=A.$('imgbq'),ems=A.$('ems');
    var l=80,r=4,c=5,s=0,p=Math.ceil(l/(r*c));
    var pt='sk/';
    bq.onclick=function(e){
        var e=e||event;
        if(!so){
            return st();
        }
        ems.style.display='block';
        document.onclick=function(){
            gb();
        }
        ct();
        try{e.stopPropagation();}catch(o){}
    }

    for(var i=0;i<p;i++){
        var a=A.$$('<a href="javascript:;">'+(i+1)+'</a>');
        ems.children[1].appendChild(a);
        ef(a,i);
    }
    ems.children[1].children[0].className='ck';

    function ct(){
        var wz=bq.weiz();
        with(ems.style){
            top=wz.y-242+'px';
            left=wz.x+bq.offsetWidth-235+'px';
        }
    }

    function ef(t,i){
        t.onclick=function(e){
            var e=e||event;
            s=i*r*c;
            ems.children[0].innerHTML='';
            hh();
            this.parentNode.children.rcss('ck','');
            this.rcss('','ck');
            try{e.stopPropagation();}catch(o){}
        }
    }

    function hh(){
        var z=Math.min(l,s+r*c);
        for(var i=s;i<z;i++){
            var a=A.$$('<img src="'+pt+i+'.gif">');
            hh1(a,i);
            ems.children[0].appendChild(a);
        }
        ct();
    }

    function hh1(t,i){
        t.onclick=function(e){
            var e=e||event;
            A.$('nrong').value+='{\\'+i+'}';
            if(!e.ctrlKey){
                gb();
            }
            try{e.stopPropagation();}catch(o){}
        }
    }

    function gb(){
        ems.style.display='';
        A.$('nrong').focus();
        document.onclick='';
    }
    hh();
    A.on(window,'resize',function(){
        A.$('ltian').style.height=(document.documentElement.clientHeight - 70)+'px';
        ct();
    })

    var fimg=A.$('upimg');
    var img=new Image();
    var dw=400,dh=300;
    A.on(fimg,'change',function(ev){
        if(!so){
            st();
            return false;
        }
        if(key=='all'){
            alert('������Դ���� ��ͼֻ��˽��');
            return false;
        }
        var f=ev.target.files[0];
        if(f.type.match('image.*')){
            var r = new FileReader();
            r.onload = function(e){
                img.setAttribute('src',e.target.result);
            };
            r.readAsDataURL(f);
        }
    });
    img.onload=function(){
        ih=img.height,iw=img.width;
        if(iw/ih > dw/dh && iw > dw){
            ih=ih/iw*dw;
            iw=dw;
        }
        else if(ih > dh){
            iw=iw/ih*dh;
            ih=dh;
        }
        var rc = A.$$('canvas');
        var ct = rc.getContext('2d');
        rc.width=iw;
        rc.height=ih;
        ct.drawImage(img,0,0,iw,ih);
        var da=rc.toDataURL();
        so.send('type=pic&nr='+esc(da)+'&key='+key);//����ͼƬ
    }

})();//js�е������﷨�����������������
</script>
</body>
</html>