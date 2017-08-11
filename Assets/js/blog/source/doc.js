if("undefined" == typeof _URL) var _URL = location.origin+'/';
LS = {

};
window.onload = function(){

	_('botao').style.display = 'block';

	_('botao').onclick = function(){swh()}
	_('menu').onclick = function(){swh()}

	document.onclick = function(e){
		if(e.target.id != "botao") {
			swh(true);
		}
	}

	_('chColor').onclick = function(e){
		e.preventDefault();
		
		if(_('stylesheet_0').href == _URL+"css/ab.css"){
			_('stylesheet_0').href = _URL+"css/a.css";
		} else {
			_('stylesheet_0').href = _URL+"css/ab.css"
		}
		return false;
	}

	//Adiciona link para Twitter nos Blockquotes
	twitterThis();

}

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

//Varre o texto e adiciona o link para Twitter em todos os blockquotes
function twitterThis(){
	var b = document.querySelectorAll('blockquote');

	for(i = 0; i < b.length; i++){
		if("undefined" != typeof b[i].innerHTML){
			b[i].innerHTML += 
            '<a class="btwitter" title="Twittar isso!" href="https://twitter.com/intent/tweet?text='+stripTag(b[i].innerHTML)+'&url='+document.location.href+'"> </a>';
		}
	}
}

//Retira caracteres html da string
function stripTag(html)
{
   var tmp = document.createElement("DIV");
   tmp.innerHTML = html.replace(/<br>/g, " ").replace(/<br\/>/g, " ").replace(/<br \/>/g, " ");
   return tmp.textContent || tmp.innerText || "";
}

function _(e) {return document.getElementById(e)}