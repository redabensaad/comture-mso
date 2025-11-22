/*
	validDate = 	this function check a string date in a specified language (and its appropriated date format)
			Returns 
				- an empty string if the date is valid
				- a string containing the required format is the date is not valid
			Exemples
				validDate( "12/31/2011", "fr" ) returns "DD/MM/YYYY"
				validDate( "12/31/2011", "en" ) returns ""
			Dependencies
				jquery.datepicker.js
*/
function validDate( sdate, lang ) {
   if( $.datepicker.regional[ lang ] == null )
     lang = "";
   if( sdate.length == 0 )
     return( "" );
   var adate = sdate.replace(/\//g, ".").replace(/-/g, ".").split(".",3);
   if( adate.length == 3 && adate[0] > 0 && adate[1] > 0 && adate[2] > 0 )
   {
     var ival,fmt,m=0,d=0,y=0;
     fmt = $.datepicker.regional[ lang ].dateFormat.toLowerCase().replace(/\//g, ".").replace(/-/g, ".").split(".",3);
     for( var i=0; i<3; i++ )
     {
       ival = parseInt( adate[i], 10 );
       if( fmt[i] == "dd" )
         d = ival;
       else if( fmt[i] == "mm" )
         m = ival;
       else // year
         y = ival;
     }
     var err = (d < 1) || (d > 31) || ( m < 1) || ( m > 12 ) ||( y <= 0 );
     if( !err ) 
        switch( m ) {
          case 2: if( !(y % 4) && (y % 100) || !(y % 400) ) { err = d > 29 } else { err = d > 28 }; break;
          case 4:
          case 6:
          case 9:
          case 11: err = d > 30; break;
        }
     if( !err )
       return( "" );
   }
   return( $.datepicker.regional[ lang ].dateFormat.toUpperCase() );
}

function convertDateToUserFormat( sdate, lang, userdatefmt ) {
   if( sdate == "" )
	  return( "" );

   if( $.datepicker.regional[ lang ] == null )
     lang = "";
   if( sdate.length == 0 )
     return( "" );
   var adate = sdate.replace(/\//g, ".").replace(/-/g, ".").split(".",3);
   if( adate.length == 3 && adate[0] > 0 && adate[1] > 0 && adate[2] > 0 )
   {
     var ival,fmt,m='',d='',y='';
     fmt = $.datepicker.regional[ lang ].dateFormat.toLowerCase().replace(/\//g, ".").replace(/-/g, ".").split(".",3);
     for( var i=0; i<3; i++ )
     {
       if( fmt[i] == "dd" )
	   {
			d = adate[i];
	   }
       else if( fmt[i] == "mm" )
	   {
			m = adate[i];
	   }
       else // year
	   {
			y = adate[i];
	   }
     }
	if( userdatefmt == "dd/mm/yyyy" ) {
		return( d + '/' + m + '/' + y );		
	} 
	return( m + '/' + d + '/' + y );
  }
}
