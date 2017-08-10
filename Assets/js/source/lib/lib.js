/*
 * Copyright Bill Rocha (2016) - Todos os direitos reservados 
 * Script by Bill Rocha   - https://google.com/+BillRocha
 * 
 */

var lib = (function(){
    var a = function(Url, Data, Token, UserId, Rsakey, EncType){
        var url =       Url     || window.location.href,
            token =     Token   || null,
            rsakey =    Rsakey  || null,
            data =      Data    || {},
            encType =   EncType || 'aes', //aes or rsa
            userId =    UserId  || 0,
            method =   'POST',            
            response =  '',            
            formdata =  new FormData(),
            node,

            onprogress = function(e){console.log(e)},
            oncomplete = function(e){console.log(e)},
            status = function(e, b){console.log(e,b)};

        //Progresss
        function progressHandler (e) {
            onprogress(Math.round((e.loaded/e.total) * 100) + "%");
        }

        //Complete
        function completeHandler (e) {
            try{
                response = JSON.parse(e.target.responseText)
            }catch(x){
                response = JSON.parse(AES.dec(e.target.responseText, token));
                if(response === false) response = e.target.responseText;
            };
            formdata = new FormData();
            data = {};
            encType = 'aes';
            oncomplete(response);
        }

        //Error
        function errorHandler (e) {
            status('Failed', e);
        }

        //Abort
        function abortHandler (e) {
            status("Aborted", e);
        }

        //Public methods
        return {
            //Add File(s)
            file: function (f){
                for(var i =0; i < f.length; i++){
                    formdata.append(i, f[i]);
                }
            },

            url: function(u){
                if("undefined" === typeof u) return url;
                url = u;
                return this;
            },
            data: function(n,d){
                if(n === false){
                    data = {}
                    return this;
                } 
                if("undefined" === typeof n) return data;
                if("object" === typeof n){
                    for(i in n){
                        data[i] = n[i];
                    }
                } else {
                data[n] = d;
                }
                return this;
            },
            method: function(m){
                if("undefined" === typeof m) return method;
                method = m;
                return this;
            },
            response: function(){
                return response
            },

            //Send
            send: function (Url) {
                if("undefined" !== typeof Url){url = Url}
                node = new XMLHttpRequest();
                node.upload.addEventListener("progress", progressHandler, false);
                node.addEventListener("load", completeHandler, false);
                node.addEventListener("error", errorHandler, false);
                node.addEventListener("abort", abortHandler, false);
                node.open(method, url);

                var dtx = data;
                if(token !== null && encType == 'aes'){
                    dtx = {id:userId, enc:AES.enc(JSON.stringify(data), token)}
                }
                if(token !== null && rsakey !== null && encType == 'rsa'){
                    data.token = token;
                    dtx = {enc:RSA.encrypt(
                                  JSON.stringify(data), 
                                  RSA.getPublicKey(rsakey))
                          }
                }
                formdata.append('data', JSON.stringify(dtx));
                node.send(formdata);
                return this;
            },
            done: function(fn){ 
                oncomplete = fn;
                return this;
            }, 
            progress: function(fn){
                onprogress = fn;
                return this;
            },
            status: function(fn){
                status = fn;
                return this;
            }
        }
    }

    var uf = function(upUrl, token, node, userId){ 

        if("undefined" === typeof upUrl ||
           "undefined" === typeof token ||
           "undefined" === typeof node ||
           "undefined" === typeof userId) {
            throw "Parametros necessários!";
            return false;
        }

        var fileMaxSize = 2000000,
            reader = [],
            frls = -1,
            debug = false;

        function listFiles(){
            var tm = 0,
                tb = '<table><tr><th>Nome do Arquivo</th><th>Tamanho</th></tr>';
            for(var i = 0; i < node.files.length; i++){
                tm += node.files[i].size;
                tb += '<tr><td>'+node.files[i].name+'</td><td>'+node.files[i].size.toLocaleString()+'</td></tr>';
            }
            if(tm > fileMaxSize) return false;
            return tb+'<tr><th>Tamanho total: </th><th>'+tm.toLocaleString()+'</th></tr></table>';
        }

        function sendFiles(callback, progress){
            //zerando contador
            frls = 0;

            for(var i = 0; i < node.files.length; i ++){
                reader[i] = new FileReader();

                //reader.readAsBinaryString(files[0]);
                reader[i].readAsArrayBuffer(node.files[i]);

                reader[i].onprogress = function(e){}
                reader[i].onloadend = function(e){
                    frls ++;
                    if(frls >= node.files.length) readerOnloadFinish(callback, progress);
                }
            }
        }

        //Processando cada arquivo
        function readerOnloadFinish(callback, progress){

            var z = new JSZip();

            for(var i = 0; i < node.files.length; i++){
                z.file(node.files[i].name, reader[i].result);
            }
            //Zipando ...
            content = z.generate({type:"base64", compression:"DEFLATE"});
            if(content.length > 2092068) return callback('O arquivo, mesmo depois de ZIPADO, ficou muito grande e não será suportado para DOWNLOAD em navegador!<br><b>Não foi enviado.</b> Tente enviar menos arquivos ou um arquivo menor.');

            var a = new lib.ajax(upUrl, {file: content}, token, userId);
            a.progress(progress)
             .done(callback)
             .send();

            reader = [];
            content = null;
        }

        return {
            fileList: function(){return listFiles()},
            send: function(callback, progress){return sendFiles(callback, progress)},

            set: {
                fileMaxSize: function(v){fileMaxSize = v},
                token: function(v){token = v}
            },

            get: {
                fileMaxSize: function(){return fileMaxSize},
                token: function(){return token}
            }
        }

    }

    var df = function(dowUrl, token, file, userId, callback){

        if("string" !== typeof file) return null;

        var a = new lib.ajax(dowUrl, {getfile:file}, token, userId);
        a.done(function(d){receiveComplete(d, callback)})
         .send();

        function receiveComplete(data, callback){
            if("object" !== typeof data) return callback('Erro no carregamento do pacote!<br>Verifique a conexão de rede.');

            if("function" === typeof navigator.msSaveOrOpenBlob){
               navigator.msSaveOrOpenBlob(JSZip.utils.string2Blob(JSZip.base64.decode(data.file)), data.name);
            } else {
                var a = document.createElement('A');
                a.href = 'data:Utillication/x-zip;base64,'+data.file;
                a.download = data.name;
                document.body.appendChild(a);

                _eclick(a);
            }

            callback('Arquivo carregado com sucesso!');
        }
    }


    //Public method
    return {
        ajax: a,
        upload: uf,
        download: df
    }
})();

function _(e) {
    return document.getElementById(e);
}
function _qa(e){
    return document.querySelectorAll(e);
}

function _qs(e){
    return document.querySelector(e);
}

/* _GFORMAT */
function _formatTxt(txt){
    txt = txt.replace(/(<)/g, "&lt;").replace(/(>)/g, "&gt;")
    txt = txt.trim().replace(/(<div>)/g,"").replace(/(<\/div>)/g,"").replace(/(<br>)/g,"\n").replace(/(\n)/g, "<br/>");
    txt = _gformat(txt, "#", ['<h1>', '</h1>']);
    txt = _gformat(txt, "*", Array("<b>", "</b>"));
    txt = _gformat(txt, "-", Array("<s>", "</s>"));
    return _gformat(txt, "_", Array("<i>", "</i>"));
}
function _gformat(txt, search, subst) {

    var init = -1;
    var fim = 0;
    var cursor = 0;
    var result = '';

    for (var i = 0; i < txt.length; i++) {
        if (txt[i] === search && init === -1 && fim === 0)
            init = i;
        if (txt[i] === search && init !== -1 && init < i) {
            fim = i;
            var temp = subst[0] + txt.substr((1 + init), (fim - init) - 1) + subst[1];
            result += txt.substr(cursor, (init - cursor)) + temp;

            cursor = (1 + fim);
            init = -1;
            fim = 0;
        }
    }
    if (txt.length > cursor)
        result += txt.substr(cursor, (txt.length - cursor));
    return result;
}

function _eclick(i){
    var e = document.createEvent("MouseEvents");
    e.initMouseEvent(
        "click", true, false, window, 0, 0, 0, 0, 0
        , false, false, false, false, 0, null
    );
    i.dispatchEvent(e);
    i.focus();
}

/** Gerador de CHAVE (senha)
 * To generate: var pass = _passw.gen(w); (w = width [integer > 0]);
 * To check:    _('text').onkeypress = function(e) { return _passw.check(String.fromCharCode(e.charCode)));}
 */

var _passw = {
    seq: '#$*%&!?@_+-=:.<>/({[]})abcdefghijklmnopqrstuvwxyz',
    alfa: '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    gen: function (w, num){ // w: width - num: numéric true/false
        o = '';
        k = "undefined" == typeof num || num === false ? this.seq + this.alfa : this.alfa;
        for(var i = 0; i < w; i++){
            o += k[Math.floor(Math.random()*k.length)];
        }
        return o;
    },
    check: function (v){
        o = false;
        k = this.seq + this.alfa;
        for(var i = 0; i < k.length; i++){
            if(k[i] == v){
                o = true;
                break;
            }
        }
        return o;
    }
}

/*
 * Mostra uma mensagem na parte superior da página por "tempo" determinado
 * Se clicar no "balão", ele também desaparece
 *
 * Se o parametro "go" for true, depois de mostrar a mensagem, chama a função "logout()".
 */
function _showMsg(t, tempo, go)
{
    tempo = "number" == typeof tempo ? tempo : 2000;

    var id = 'msg'+_passw.gen(10, true);
    var m = document.createElement('DIV');
    m.id = id;
    m.innerHTML = "<b>"+USER.name+"</b><br>"+t;
    m.className = 'alert on';
    var on = document.createAttribute('onclick');
    on.value = "this.outerHTML=''";
    m.attributes.setNamedItem(on);
    document.body.appendChild(m);

    if("undefined" === typeof go || go === false){
        setTimeout(function(){document.getElementById(id).outerHTML = ''}, tempo);
    } else {
        setTimeout(function(){logout()}, tempo);
    }
}


/*
 * Copyright Bill Rocha (2016) - Todos os direitos reservados 
 * Script by Bill Rocha   - https://google.com/+BillRocha
 * 
 */