"use strict";(self.webpackChunk_metafox_react=self.webpackChunk_metafox_react||[]).push([["metafox-livestreaming-components-FlyReaction"],{43498(t,e,i){i.d(e,{Ui:()=>h,ET:()=>f}),i(96602);var o=i(96540),a=i(91557),n=i(70223),s=i(11497),r=i(656),l=i(78292);let c=new class{bootstrap(t,e){this.manager=t,this.settingApp=this.manager.getSetting("firebase"),this.settingApp&&this.init(this.settingApp,e)}checkActive(){return this.isActive}init(t,e){if(this.mounted)return this.firebaseApp;this.mounted=!0;let i={apiKey:a.z0J,authDomain:a.wRL,projectId:a.wWR,storageBucket:a.npo,messagingSenderId:a.AfL,appId:a.Kou};this.config=t;try{let o=(0,n.Wp)(i),s=(0,r.xI)(o),{user_firebase_email:l,user_firebase_password:c,requiredSignin:p}=this.settingApp||{};return p&&(0,r.x9)(s,l,c).then(t=>{}).catch(t=>{console.log("Live Video - Firebase signInWithEmailAndPassword error",t),"auth/user-not-found"===t.code&&(0,r.eJ)(s,l,c)}),e(!0),this.firebaseApp=o,this.isActive=!0,o}catch(u){this.isActive=!1}}getFirebaseApp(){return this.firebaseApp}getFirestore(){if(!this.firebaseApp)return null;if(!this.firestore){let t=(0,s.aU)(this.firebaseApp);t&&(this.firestore=t)}return this.firestore}getMessaging(){if(!this.firebaseApp)return null;if(!this.cloudMessage){let t=(0,l.dG)(this.firebaseApp);t&&(this.cloudMessage=t)}return this.cloudMessage}},p=!1,u=!1;function h(){let[t,e]=function(){let t=(0,a.PCX)(),[e,i]=o.useState(!1);return e||p||(p=!0,c.bootstrap(t,()=>{u=!0,i(!0)})),[e||u,c]}();if(!t)return!1;let i=e.getFirestore();return i}let d=(t,e)=>{let[i,a]=(0,o.useState)();return(0,o.useEffect)(()=>{let i;if(t&&(null==e?void 0:e.collection)&&(null==e?void 0:e.docID)){try{let o=(0,s.H9)(t,e.collection,e.docID);i=(0,s.aQ)(o,async t=>{a(t.data())})}catch(n){}return()=>{null==i||i()}}},[]),i},f=d},67629(t,e,i){i.r(e),i.d(e,{default:()=>f});var o=i(96540),a=i(3556),n=i(8051),s=i(87773),r=i(91557),l=i(88980),c=i(43498);let p=(0,l.i7)`
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
`,u="FlyReaction",h=(0,a.Ay)(n.A,{name:u,slot:"ReactionIcon",shouldForwardProp:t=>"index"!==t})(({theme:t,index:e})=>({display:"flex",alignItems:"center",justifyContent:"center",position:"absolute",animation:`${p} linear 2s forwards `,animationDelay:`${20*Math.floor(99*Math.random())}ms`,left:`calc(50% - ${Math.max(10*Math.floor(10*Math.random()),30)}%)`,bottom:"-32px",width:"24px",height:"24px","& img":{width:"100%",height:"100%"}})),d=(0,a.Ay)(s.A,{name:u,slot:"Wrapper",shouldForwardProp:t=>"backgroundColor"!==t})(({theme:t})=>({position:"absolute",left:"24px",bottom:0,width:"100px",height:"70%",pointerEvents:"none"})),f=function({streamKey:t,identity:e}){let{dispatch:i}=(0,r.PCX)(),a=(0,c.Ui)(),n=(0,c.ET)(a,{collection:"live_video_like",docID:t}),s=(null==n?void 0:n.like)||[],l=s.slice(Math.max(s.length-20,0));return(o.useEffect(()=>{i({type:"livestreaming/updateStatistic",payload:{identity:e,most_reactions_information:(null==n?void 0:n.most_reactions_information)||[],statistic:(null==n?void 0:n.statistic)||{total_like:null==n?void 0:n.total_like}}})},[null==n?void 0:n.total_like,null==n?void 0:n.statistic,null==n?void 0:n.most_reactions_information]),t&&(null==l?void 0:l.length))?o.createElement(d,null,l.map(({reaction:{icon:t,title:e,id:i}},a)=>o.createElement(h,{key:`live_reaction_${a}`,index:a},o.createElement("img",{src:t,alt:e})))):null}}}]);