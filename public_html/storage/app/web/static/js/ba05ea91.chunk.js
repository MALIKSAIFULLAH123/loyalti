"use strict";(self.webpackChunk_metafox_react=self.webpackChunk_metafox_react||[]).push([["metafox-livestreaming-components-FlyReaction"],{43498(t,e,i){let o;i.d(e,{LL:()=>m,Ui:()=>g,ET:()=>v});var n=i(96602),a=i(96540),r=i(91557),s=i(70223),l=i(11497),c=i(656),u=i(78292);let p=new class{bootstrap(t,e){this.manager=t,this.settingApp=this.manager.getSetting("firebase"),this.settingApp&&this.init(this.settingApp,e)}checkActive(){return this.isActive}init(t,e){if(this.mounted)return this.firebaseApp;this.mounted=!0;let i={apiKey:r.z0J,authDomain:r.wRL,projectId:r.wWR,storageBucket:r.npo,messagingSenderId:r.AfL,appId:r.Kou};this.config=t;try{let o=(0,s.Wp)(i),n=(0,c.xI)(o),{user_firebase_email:a,user_firebase_password:l,requiredSignin:u}=this.settingApp||{};return u&&(0,c.x9)(n,a,l).then(t=>{}).catch(t=>{console.log("Live Video - Firebase signInWithEmailAndPassword error",t),"auth/user-not-found"===t.code&&(0,c.eJ)(n,a,l)}),e(!0),this.firebaseApp=o,this.isActive=!0,o}catch(p){this.isActive=!1}}getFirebaseApp(){return this.firebaseApp}getFirestore(){if(!this.firebaseApp)return null;if(!this.firestore){let t=(0,l.aU)(this.firebaseApp);t&&(this.firestore=t)}return this.firestore}getMessaging(){if(!this.firebaseApp)return null;if(!this.cloudMessage){let t=(0,u.dG)(this.firebaseApp);t&&(this.cloudMessage=t)}return this.cloudMessage}},h=!1,f=!1;function d(){let t=(0,r.PCX)(),[e,i]=a.useState(!1);return e||h||(h=!0,p.bootstrap(t,()=>{f=!0,i(!0)})),[e||f,p]}function m(){let[t,e]=a.useState(),[i,r]=a.useState(!1),[s,l]=d(),c=a.useRef(0);if(!s)return["",()=>{},!0];if(!o&&!i&&s)try{o=l.getMessaging()}catch(u){r(!0)}let p=t=>{(0,n.gf)(o).then(i=>{t(i),e(i)}).catch(()=>{c.current>5||(c.current=c.current+1,p(t))})};return[t,p,i]}function g(){let[t,e]=d();if(!t)return!1;let i=e.getFirestore();return i}let b=(t,e)=>{let[i,o]=(0,a.useState)();return(0,a.useEffect)(()=>{let i;if(t&&(null==e?void 0:e.collection)&&(null==e?void 0:e.docID)){try{let n=(0,l.H9)(t,e.collection,e.docID);i=(0,l.aQ)(n,async t=>{o(t.data())})}catch(a){}return()=>{null==i||i()}}},[]),i},v=b},67629(t,e,i){i.r(e),i.d(e,{default:()=>d});var o=i(96540),n=i(3556),a=i(8051),r=i(87773),s=i(91557),l=i(88980),c=i(43498);let u=(0,l.i7)`
    0% {
        bottom:0;
        opacity: 1;
    }
    30% {
        transform:translateX(30px);
        bottom: 30%;
        opacity: 1
    }
    70% {
       transform:translateX(0px);
       bottom: 70%;
       opacity: 1
    }
    100% {
        transform:translateX(30px);
        bottom: 100%;
        opacity: 0;
    }
`,p="FlyReaction",h=(0,n.Ay)(a.A,{name:p,slot:"ReactionIcon",shouldForwardProp:t=>"index"!==t})(({theme:t,index:e})=>({display:"flex",alignItems:"center",justifyContent:"center",position:"absolute",animation:`${u} linear 2s forwards `,animationDelay:`${20*Math.floor(99*Math.random())}ms`,left:`calc(50% - ${Math.max(10*Math.floor(10*Math.random()),30)}%)`,bottom:"-32px",width:"24px",height:"24px","& img":{width:"100%",height:"100%"}})),f=(0,n.Ay)(r.A,{name:p,slot:"Wrapper",shouldForwardProp:t=>"backgroundColor"!==t})(({theme:t})=>({position:"absolute",left:"24px",bottom:0,width:"100px",height:"70%",pointerEvents:"none"})),d=function({streamKey:t,identity:e}){let{dispatch:i}=(0,s.PCX)(),n=(0,c.Ui)(),a=(0,c.ET)(n,{collection:"live_video_like",docID:t}),r=(null==a?void 0:a.like)||[],l=r.slice(Math.max(r.length-20,0));return(o.useEffect(()=>{i({type:"livestreaming/updateStatistic",payload:{identity:e,most_reactions_information:(null==a?void 0:a.most_reactions_information)||[],statistic:(null==a?void 0:a.statistic)||{total_like:null==a?void 0:a.total_like}}})},[null==a?void 0:a.total_like,null==a?void 0:a.statistic,null==a?void 0:a.most_reactions_information]),t&&(null==l?void 0:l.length))?o.createElement(f,null,l.map(({reaction:{icon:t,title:e,id:i}},n)=>o.createElement(h,{key:`live_reaction_${n}`,index:n},o.createElement("img",{src:t,alt:e})))):null}}}]);