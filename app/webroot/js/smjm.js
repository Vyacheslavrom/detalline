$(function(){

	$( "#kabinet" ).dialog({
		autoOpen: false,
		modal: true,
		position: ['right',35]
	});

	$( "#openLoginFormLink" ).click(function() {
			$( "#kabinet" ).dialog( "open" );
			return false;
		});

	$( "#openKabinetLink" ).click(function() {
			$( "#kabinet" ).dialog( "open" );
			return false;
		});
});

function openWindow(link,w,h) //opens new window
{
	var win = "width="+w+",height="+h+",menubar=no,location=no,resizable=yes,scrollbars=yes";
	newWin = window.open(link,'newWin',win);
	newWin.focus();
}

function showHideBlock(id) {
	if($('#'+id).css("display") == "none") 
		$('#'+id).css({"display":"block"});
	else
		$('#'+id).css({"display":"none"});
	return false;
}

 function choose_model(id, sel, sp, selObj)
 {	
        selObj.disabled = true;											  
	$.ajax({
		type: "GET",
		async: true,
		url: "/choose_models.html",
		data: {id: id, sel: sel},
		success: function(data, status) {
		              sp.html(data);
                             selObj.disabled = false;	
		}

	});
  }


