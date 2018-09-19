function ResetDomain()
{	var ss=document.domain;						//ss == "www.126.com";
	var ii=ss.lastIndexOf('.');
	if(ii>0)
	{	if(!isNaN(ss.substr(ii+1)*1))
			return;
		ii=ss.lastIndexOf('.',ii-1);
		if(ii>0)
			document.domain	=ss.substr(ii+1);
	}											//document.domain == "126.com";
}
ResetDomain();
