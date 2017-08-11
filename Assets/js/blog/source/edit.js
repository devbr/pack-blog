if("undefined" == typeof _URL) var _URL = location.origin+'/';

var edtContent = new MediumEditor('.edtContent', {
	buttonLabels: 'fontawesome',
	placeholder: { text: 'Comece a digitar aqui o conteúdo da publicação ...' },
    toolbar: {
      buttons: [
        'bold',
        'italic',
        'underline',
        'anchor',
        'h2',
        'h3',
        'quote',
        'pre',
        'orderedlist',
        'unorderedlist',
        'justifyLeft',
        'justifyCenter',
        'justifyRight',
        'justifyFull',
        'table',
        'removeFormat'
      ]
    },
    anchor: {
        targetCheckbox: true
    },
    extensions: {
        'imageDragging': {},
        table: new MediumEditorTable()
    }
});

var edtTitle = new MediumEditor('.edtTitle',{
	buttonLabels: 'fontawesome',
	toolbar: { buttons: ['italic','underline','removeFormat'] },
	placeholder: { text: 'Título da Publicação ...' }
});

var edtDestaque = new MediumEditor('.edtDestaque',{
    buttonLabels: 'fontawesome',
    toolbar: { buttons: ['italic','underline','removeFormat'] },
    placeholder: { text: 'Texto de destaque e resumo da aplicação ...' }
});


var EDIT = {};
        

$(function () {

    _('botao').style.display = 'block';

    EDIT.info = {user: uID, article: aID};
    EDIT.media = {};
    EDIT.title = document.querySelector('.edtTitle').innerHTML
    EDIT.destaque = '';
    EDIT.content = '';
    EDIT.link = '';

    // Menu - begin --------------
    _('botao').onclick = function(){swh()}
    _('menu').onclick = function(){swh()}

    document.onclick = function(e){
        if(e.target.id != "botao") {
            swh(true);
        }
    }

    _('chColor').onclick = function(e){
        e.preventDefault();
        
        if(_('stylesheet_0').href == _URL+"css/eb.css"){
            _('stylesheet_0').href = _URL+"css/e.css";
        } else {
            _('stylesheet_0').href = _URL+"css/eb.css"
        }
        return false;
    }

    // Menu - end --------------


    $('.edtContent').mediumInsert({
        editor: edtContent,
        addons: {
            images: {
                deleteScript: _URL+'x/delete/'+aID,
        		fileUploadOptions: {
	                url: _URL+'x/upload/'+aID, 
	                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                    maxFileSize: 1900000
	            },
                captionPlaceholder: 'Digite uma descrição para a imagem',
                styles: {
                	 wide: {
						label: '<span class="fa fa-align-justify"></span>'
 						},
                    slideshow: {
                        label: '<span class="fa fa-play"></span>',
                        added: function ($el) {
                            $el
                                .data('cycle-center-vert', true)
                                .cycle({
                                    slides: 'figure'
                                });
                        },
                        removed: function ($el) {
                            $el.cycle('destroy');
                        }
                    }
                },
                actions: null
            }
        }
    });

    $('#txtlink').on('change', function(e){
        $('#save').hide();
        $('#link').fadeIn();
        var cont = $('#txtlink').val().trim();

        if(cont == "") {
        	cont = $('#edtTitle').html().trim();
        	$('#txtlink').val(cont);
        }
        checklink(cont);
    })

    $('#edtTitle').on('blur', function(e){
		var v = $('#edtTitle').html().trim();
    	
    	if(EDIT.title != v) EDIT.title = v;

    	$('#txtlink').val(v);
     })

    $('#link').on('click', function(){ checklink($('#txtlink').val().trim()) })
    $("#cancel").on('click', function(){ window.location = _URL+'a/'+pageLink;})
    $('#save').on('click', function(){salvar()});

});


//Pega o titulo e checa se pode ser usado como link
function getTitle() {
    t = document.getElementById('edtTitle');
    if(EDIT.title != t.innerHTML){
        EDIT.title = t.innerHTML;
        checklink(t.innerHTML);
    } 
}


//Verifica se o link permanente pode ser usado
function checklink(title){
    $('#txtlink').val('aguarde ...');

    title = title.replace(/ /g, '-')
                .replace(/'/g, '')
                .replace(/&nbsp;/g, '')
                .toLowerCase();

    $.ajax(_URL+'x/checklink', {method:"POST", data:{link:title, aID:aID}})
    .done(function(d){

        $('#txtlink').val(d.link);

        if(d.status == 'ok'){
            
            $('#link').hide()
            $('#save').fadeIn();

            EDIT.link = d.link;
        } else {

            $('#save').hide();
            $('#link').fadeIn();

            EDIT.link = null;
        }
    })
}

//Save edition
function salvar() {
    var b = [];
    var e = "";
    var a = document.querySelector(".edtContent");
    if (null != a) {
        var d = a.querySelectorAll("figure");
        var c = 0;
        d.forEach(function(j) {
            var g, h;
            var f = j.querySelector("img");
            if (null == f) {
                f = j.querySelector("iframe");
                g = "video"
            } else {
                g = "image"
            }
            if (g != null) {
                h = f.src
            }
            var k = j.querySelector("figcaption");
            if (k != null) {
                e = k.innerHTML
            }
            b[c] = {
                type: g,
                src: h,
                caption: e
            };
            c++
        })
    }
    EDIT.info = {
        user: uID,
        article: aID
    };
    EDIT.media = b;
    EDIT.title = document.querySelector(".edtTitle").innerHTML;
    EDIT.destaque = document.querySelector(".edtDestaque").innerHTML;
    EDIT.content = edtContent.serialize()["element-0"]["value"];
    EDIT.category = $("#category").val();
    EDIT.tags = $("#txttags").val();
    EDIT.status = $("#status").val();
    if (EDIT.link == "") {
        EDIT.link = pageLink
    }

    //Conversão
    var tmp = {};
    tmp.dt = btoa(JSON.stringify(EDIT));

    $.ajax(_URL + "x/save", {
        method: "POST",
        data: tmp
    }).done(function(f) {
        if (f.status == "ok") {
            $("#msg").html("Edição salva!").fadeIn().fadeOut(5000);
            setTimeout(function() {
                location.href = _URL + "a/" + f.link
            }, 2000);
            $("#save").hide()
        } else {
            $("#msg").html("Não consegui salvar!").fadeIn().fadeOut(5000)
        }
    });
    return false
}


//Gerencia o menu ocultável
function swh(open){
    var m = _('menu');
    
    if(open){
        m.classList.remove('show');
        m.classList.add('hide');
        return;
    }

    if ('hide' == m.classList[1]) {
        m.classList.remove('hide');
        m.classList.add('show');
    } else {
        m.classList.remove('show');
        m.classList.add('hide');
    }
}


//Retorna o objeto indicado pelo ID
function _(e) {return document.getElementById(e)}