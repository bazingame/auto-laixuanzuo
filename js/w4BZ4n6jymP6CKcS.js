var _="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz",f={0:0,1:1,2:2,3:3,4:4,5:5,6:6,7:7,8:8,9:9,A:10,B:11,C:12,D:13,E:14,F:15,G:16,H:17,I:18,J:19,K:20,L:21,M:22,N:23,O:24,P:25,Q:26,R:27,S:28,T:29,U:30,V:31,W:32,X:33,Y:34,Z:35,a:36,b:37,c:38,d:39,e:40,f:41,g:42,h:43,i:44,j:45,k:46,l:47,m:48,n:49,o:50,p:51,q:52,r:53,s:54,t:55,u:56,v:57,w:58,x:59,y:60,z:61};Hexch=function(t){if(t.length<57)throw new Error("the key is too short.");this._sz=_.charCodeAt(t[15])%(t.length-20)+10,this._ks=t.slice(-this._sz);for(var r=0;r<this._sz;++r)this._ks[r]=_.charCodeAt(this._ks[r]%62);this._k16=[],this._k41=[],this._t16={},this._t41={};for(r=0;r<16;++r)this._k16[r]=_.charAt(t[r]),this._t16[this._k16[r]]=r;for(r=0;r<41;++r)this._k41[r]=_.charAt(t[r+16]),this._t41[this._k41[r]]=r},Hexch.prototype.dec=function(t){for(var r=this._t16,h=this._t41,s=this._ks,e=this._sz,i=0,o=t.replace(/[0-9A-Za-z]/g,function(t){return _.charAt((f[t]-s[i++%e]%62+62)%62)}),a="",c=0;c<o.length;){var n=o.charAt(c);/[\s\n\r]/.test(n)?(a+=n,++c):void 0!==r[n]?(a+=String.fromCharCode(16*r[o.charAt(c)]+r[o.charAt(c+1)]),c+=2):(a+=String.fromCharCode(1681*h[o.charAt(c)]+41*h[o.charAt(c+1)]+h[o.charAt(c+2)]),c+=3)}return a},reserve_seat=function(h,s,t){void 0===t&&(t="");var r=JSON.parse("[4,50,27,58,10,9,19,56,32,49,46,16,37,33,1,2,55,29,57,60,23,59,44,38,31,43,25,6,5,17,24,0,54,61,52,13,26,42,41,8,39,11,21,53,40,35,51,15,30,18,48,45,28,7,14,22,34,36,12,20,47,3]"),e=new Hexch(r);console.log(e.dec("mO79JU4VVK1gkaE13q"))};reserve_seat()reserve_seat()