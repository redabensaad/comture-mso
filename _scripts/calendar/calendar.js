function showCal(year,month) {
	var now = new Date();
	var currYear = now.getUTCFullYear();
	var currMonth = now.getUTCMonth()+1;
	var currDay = now.getDate();
	if( month == undefined )
		month = currMonth;
	if( year== undefined )
		year = currYear;
	function getDaysInMonth(m, y) {
	   return /8|3|5|10/.test(--m)?30:m==1?(!(y%4)&&y%100)||!(y%400)?29:28:31;
	}
	function matchEvent( ev, dayStr, dayDt ) {
		if( dayStr >= ev.start && dayStr <= ev.end ) {
			if( ev.days !== undefined ) {
				var jsday = dayDt.getDay()-1;
				if(jsday<0)
					jsday = 6;
				return ev.days.substr( jsday,1 ) == '1';
			}
			return true;
		}
	}
	var entityMap={"&":"&amp;","<":"&lt;",">":"&gt;",'"':'&quot;',"'":'&#39;',"/":'&#x2F;'};
	function escapeHtml(string) {
		return String(string).replace(/[&<>"'\/]/g, function (s) {return entityMap[s];});
	}	
	var offset = new Date(year, month-1, 1,1,0,0,0).getDay()-1;
	if( offset < 0) 
		offset = 6;
	var daysInMonth = getDaysInMonth(month, year);
	var nav = '<div class="btn-group">' +
		'<a class="btn" href="#" onclick="showCal(' + (month==1?year-1:year) + ',' + (month==1?12:month-1)+ ');return false;"><i class="icon-arrow-'+(isRtl ?'right':'left')+'"><!----></i></a>' +
		'<a class="btn" style="min-width:150px" href="#" onclick="showCal(); return false;"><b>' + ( showMonthAfterYear ? (year + ' ' + monthNames[month-1]) : (monthNames[month-1] + ' ' + year) )  + '</b></a>' +
		'<a class="btn" href="#" onclick="showCal(' + (month==12?year+1:year) + ',' + (month==12?1:month+1)+ ');return false;"><i class="icon-arrow-'+(isRtl ?'left':'right')+'"><!----></i></a>' +
		'</div>';
	var calFull = '<table class="hidden-phone" style="width:100%;table-layout:fixed"><tr class="cal-header cal-hline">';
    var calRwd = '<table class="visible-phone" style="width:100%">';
	for(var i=0;i< 7;i++)
		calFull += '<th' + (i<6?' class="cal-vline'+(isRtl ?' rtl"':'"'):'') + '>' + dayNames[(i+1==7)?0:i+1] + '</th>';
	calFull += '</tr>';
    var rows = Math.ceil( ( offset + daysInMonth ) / 7 );
    for(var i=1;i<=rows;i++) {
      if((i-1)*7+1-offset>daysInMonth)
      	break;
      calFull += '<tr'+ (i<rows?' class="cal-hline"':'') + '>';
      for(var j=1;j<=7;j++) {
      	var day = (i-1)*7+j-offset;
       	var dayOfWeek = new Date(year, month-1, day,1,0,0,0).getDay();
       	calFull += '<td class="cal-cell';
        if( day > 0 && day <= daysInMonth ) {
        	var dd = (day<10?'0'+day:day).toString();
        	var mm = (month<10?'0'+month:month).toString();
        	var dayOfWeek = new Date(year, month-1, day,1,0,0,0).getDay();
		    var strEv = '';
		    var strEvFull = '';
        	var dayDt = new Date(year, month-1, day,1,0,0,0);
	        var dayString = 
	        	year.toString() + '-' + 
	        	(month<10?'0'+month:month).toString() + '-' +
	        	(day<10?'0'+day:day).toString();
        	for(var ev=0;ev<events.length; ev++) {
        		var event = events[ev];
        		if( matchEvent(event, dayString, dayDt ) ){
					strEv += '<span class="label label-' + event.color + '" title="' + event.summary + '">' + event.summary + '</span><br>';
					strEvFull += '<span class="label label-' + event.color + '">' + event.summary + '</span><p>' + event.description;
					if( event.textlink === undefined || event.linkurl === undefined ) {
					} else if( event.textlink != '' && event.linkurl !='' ) 
						strEvFull += '<br><a href="'+event.linkurl+'">'+event.textlink+'</a>'; 
					strEvFull += '</p><br>';
        		}
	        }
        	var modalScript = '';
	        if( strEv != '' ) {
	         	modalScript = 
	         		' onclick="' +
	         			'$(\'#modalEv h3\').html(\''+ escapeHtml( dayNames[dayOfWeek] + ' ' + shortDateFormat.replace(/dd/,dd ).replace(/mm/,mm).replace(/yy/,year) ) +'\');' +
	         			'$(\'#modalEv .modal-body p\').html(\''+ escapeHtml(strEvFull) +'\');' +
		         		'$(\'#modalEv\').modal();' +
	         		'"';
	        }
	       	calFull += 
	       		( strEv != '' ? ' cal-clickable-cell' : '') +
	        	( (day==currDay&&month==currMonth&&year==currYear) ? ' cal-today' : '' ) +
	        	( j < 7 ?' cal-vline'+(isRtl ?' rtl':''):'') + '" style="overflow:hidden;padding:8px;vertical-align:top"' +
	        	modalScript +
	        	'>';
        	calFull += '<h2>' + day + '</h2>';
			calRwd += 
			  '<tr class="cal-cell'+ 
	       		( strEv != '' ? ' cal-clickable-cell' : '') +
			  	( dayOfWeek ==0 ? ' cal-sunday' : '' ) +
			  	( (day==currDay&&month==currMonth&&year==currYear) ? ' cal-today' : '' ) +
			  	( day < daysInMonth ? ' cal-hline' : '' ) + 
	        	modalScript +
			  	'">' +
			    '<td class="cal-vline'+(isRtl ?' rtl':'')+'" style="padding:0 8px 0 8px;white-space:nowrap;vertical-align:top;"><h2>' + day + ' <small>' +
			    dayNamesShort[ dayOfWeek ] + 
			    '</small></h2></td>' +
			    '<td style="padding-left:8px;width:100%;"' + modalScript + '>';
        	calFull += strEv;
			calRwd += strEv;
		    calRwd += '</td></tr>';
        }
        else
	        calFull += 
	        	( dayOfWeek ==0 ? ' cal-sunday' : '' ) +
	        	( j < 7 ?' cal-vline'+(isRtl ?' rtl':''):'') + '" style="overflow:hidden;padding:8px;vertical-align:top"' +
	        	'>';
        calFull += '</td>';
	  }
      calFull += '</tr>';
	}    
    calFull += '</table>';
    calRwd += '</table>';
	$('#top-content .cal-nav').html(nav);
	$('#calendar').html(calFull+calRwd);
	if( typeof( $('#calendar').swiperight ) !== 'undefined') {
	    $('#calendar').swiperight(function() {showCal( (month==1)?year-1:year,(month==1)?12:month-1)} );
	    $('#calendar').swipeleft(function() {showCal( (month==12)?year+1:year,(month==12)?1:month+1)} );
	}
}
