var _="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz",f={0:0,1:1,2:2,3:3,4:4,5:5,6:6,7:7,8:8,9:9,A:10,B:11,C:12,D:13,E:14,F:15,G:16,H:17,I:18,J:19,K:20,L:21,M:22,N:23,O:24,P:25,Q:26,R:27,S:28,T:29,U:30,V:31,W:32,X:33,Y:34,Z:35,a:36,b:37,c:38,d:39,e:40,f:41,g:42,h:43,i:44,j:45,k:46,l:47,m:48,n:49,o:50,p:51,q:52,r:53,s:54,t:55,u:56,v:57,w:58,x:59,y:60,z:61};Hexch=function(t){if(t.length<57)throw new Error("the key is too short.");this._sz=_.charCodeAt(t[15])%(t.length-20)+10,this._ks=t.slice(-this._sz);for(var r=0;r<this._sz;++r)this._ks[r]=_.charCodeAt(this._ks[r]%62);this._k16=[],this._k41=[],this._t16={},this._t41={};for(r=0;r<16;++r)this._k16[r]=_.charAt(t[r]),this._t16[this._k16[r]]=r;for(r=0;r<41;++r)this._k41[r]=_.charAt(t[r+16]),this._t41[this._k41[r]]=r},Hexch.prototype.dec=function(t){for(var r=this._t16,s=this._t41,h=this._ks,e=this._sz,i=0,o=t.replace(/[0-9A-Za-z]/g,function(t){return _.charAt((f[t]-h[i++%e]%62+62)%62)}),c="",a=0;a<o.length;){var n=o.charAt(a);/[\s\n\r]/.test(n)?(c+=n,++a):void 0!==r[n]?(c+=String.fromCharCode(16*r[o.charAt(a)]+r[o.charAt(a+1)]),a+=2):(c+=String.fromCharCode(1681*s[o.charAt(a)]+41*s[o.charAt(a+1)]+s[o.charAt(a+2)]),a+=3)}return c},reserve_seat=function(s,h,t){void 0===t&&(t="");var r=JSON.parse("[45,5,53,56,39,36,46,50,59,23,43,18,28,15,34,0,54,42,3,4,44,58,52,57,9,24,51,29,6,47,48,7,8,33,31,35,11,16,41,40,1,21,27,61,55,12,10,25,13,20,17,26,19,38,49,32,2,30,37,14,22,60]"),e=new Hexch(r);console.log(e.dec("gzqq4DwSP2O0KCzuxs01uDpEgz"))};reserve_seat()