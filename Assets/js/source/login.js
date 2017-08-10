var CONT = 0;
var TENTA = 3;

window.onload = function(){

	//_('botao').style.display = 'block';

	_('btlogin').onclick = function(){
        _('nogin').style.display = 'block';
        _('noginTxt').innerHTML = "Invalid password!";
        TENTA --;
        if(TENTA == 0) {
        	TENTA = 3;
        	CONT = 60;
        	cont();
        }
    }
    _('nogin').onclick = function(){
    	if(CONT > 0) return false;
        _('nogin').style.display = 'none';
    }


    function cont(){

    	_('noginTxt').innerHTML = "Wait... ( "+(CONT < 10 ? "0"+CONT : CONT)+" )";

    	if(CONT > 0){
    		setTimeout(cont, 900);
    		CONT--;
    		//console.log("IF: \nCont:"+CONT+"\nTENTA:"+TENTA);	
    	} else { 
    		CONT = 0;
    		TENTA = 3;
    		_('nogin').style.display = 'none';	

    		//console.log("Else: \nCont:"+CONT+"\nTENTA:"+TENTA);	
    	}
    }
	
}

function _(e) {return document.getElementById(e)}