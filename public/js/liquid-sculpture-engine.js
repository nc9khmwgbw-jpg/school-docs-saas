/**
 * Liquid Sculpture WebGL2 Particle Simulation Engine
 * Based on @gughigug/liquid-sculpture-hero (Forma Studio)
 */
const i = (fn, name) => fn;
const X = (obj, prop, val) => { obj[prop] = val; return val; };
function xt(S,s,h){return Math.min(h,Math.max(s,S))}i(xt,"clamp");function Hh(S,s){if(typeof S!="string")return s;const h=S.trim();let o=h.match(/^#?([0-9a-f]{3})$/i);if(o){const[E,_,O]=o[1].split("").map(Y=>parseInt(Y+Y,16)/255);return[E,_,O]}if(o=h.match(/^#?([0-9a-f]{6})([0-9a-f]{2})?$/i),o){const E=parseInt(o[1],16);return[(E>>16&255)/255,(E>>8&255)/255,(E&255)/255]}return o=h.match(/^rgba?\(\s*([\d.]+)[,\s]+([\d.]+)[,\s]+([\d.]+)/i),o?[+o[1]/255,+o[2]/255,+o[3]/255]:s}i(Hh,"hexToRgb");const Yf=`
float hash(vec2 p){ p = fract(p*vec2(123.34,456.21)); p += dot(p,p+45.32); return fract(p.x*p.y); }
float vnoise(vec2 p){ vec2 i=floor(p); vec2 f=fract(p); vec2 u=f*f*f*(f*(f*6.0-15.0)+10.0);
  return mix(mix(hash(i),hash(i+vec2(1.0,0.0)),u.x), mix(hash(i+vec2(0.0,1.0)),hash(i+vec2(1.0,1.0)),u.x), u.y); }
vec2 ambientField(vec2 uv, float t){
  vec2 q = uv*vec2(3.2,1.7);
  vec2 a = vec2(vnoise(q+vec2(t*0.11,0.0))-0.5, vnoise(q+vec2(5.2,t*0.08)+3.1)-0.5);
  vec2 q2 = uv*vec2(7.5,4.0) + vec2(-t*0.19, t*0.13);
  vec2 b = vec2(vnoise(q2+1.7)-0.5, vnoise(q2+9.3)-0.5);
  return a + b*0.45;
}
`,Gh=`
float energyAt(vec2 rectPx, vec2 mouse, float radius, float presence, float wakeEnergy){
  vec2 d = rectPx - mouse;
  float f = exp(-dot(d,d)/(radius*radius))*presence;
  return smoothstep(0.06, 0.5, f*1.15 + wakeEnergy*1.7);
}
`,Hf=`#version 300 es
layout(location=0) in vec2 aPos;
out vec2 vUv;
void main(){ vUv = aPos*0.5+0.5; gl_Position = vec4(aPos,0.0,1.0); }
`,bm=`#version 300 es
precision highp float;
in vec2 vUv;
out vec4 o;
uniform sampler2D uPrev;
uniform vec2 uTexel;
uniform vec2 uMouse;
uniform vec2 uVel;
uniform vec2 uSize;
uniform float uRadius;
uniform float uPresence;
uniform float uDecay;
vec2 decV(vec4 c){ return (c.rg*255.0-128.0)/127.0; }
void main(){
  vec2 v0 = decV(texture(uPrev, vUv));
  vec2 uv = vUv - v0*uTexel*2.5;
  vec4 c = texture(uPrev, uv)*0.52
    + 0.12*(texture(uPrev, uv+vec2(uTexel.x,0.0)) + texture(uPrev, uv-vec2(uTexel.x,0.0))
          + texture(uPrev, uv+vec2(0.0,uTexel.y)) + texture(uPrev, uv-vec2(0.0,uTexel.y)));
  vec2 v = decV(c);
  float p = c.b;
  vec2 d = (vUv-uMouse)*uSize;
  float f = exp(-dot(d,d)/(uRadius*uRadius))*uPresence;
  float sp = clamp(length(uVel)*2.5, 0.0, 1.0);
  v += uVel*f*0.22;
  p = max(p, f*sp);
  v *= uDecay;
  p *= uDecay;
  v = sign(v)*max(abs(v)-1.2/127.0, 0.0);
  p = max(p-1.2/255.0, 0.0);
  v = clamp(v, -1.0, 1.0);
  o = vec4((v*127.0+128.0)/255.0, p, 1.0);
}
`,pm=`#version 300 es
precision highp float;
out vec4 o;
uniform sampler2D uState;
uniform sampler2D uHome;
uniform sampler2D uFlow;
uniform vec2 uSize;
uniform vec2 uMouse;
uniform float uRadius;
uniform float uPresence;
uniform float uStrength;
uniform float uSpread;
uniform float uTurb;
uniform float uReturn;
uniform float uTime;
uniform float uDt;
uniform float uIntro;
${Yf}
vec2 curl(vec2 p){
  const float e = 0.35;
  float a = vnoise(p+vec2(0.0,e)) - vnoise(p-vec2(0.0,e));
  float b = vnoise(p+vec2(e,0.0)) - vnoise(p-vec2(e,0.0));
  return vec2(a, -b)/(2.0*e);
}
void main(){
  ivec2 ij = ivec2(gl_FragCoord.xy);
  vec4 hm = texelFetch(uHome, ij, 0);
  if(hm.w <= 0.0){ o = vec4(0.0); return; }
  vec4 st = texelFetch(uState, ij, 0);
  vec2 off = st.xy;
  vec2 vel = st.zw;
  float sd = hm.z;
  float light = 0.45 + sd*1.1;

  vec2 p = hm.xy*uSize + off;
  vec4 fl = texture(uFlow, clamp(p/uSize, 0.0, 1.0));
  vec2 wake = (fl.rg*255.0-128.0)/127.0;
  float pr = fl.b;

  vec2 toM = p - uMouse;
  float r2 = dot(toM,toM);
  float f = exp(-r2/(uRadius*uRadius))*uPresence;
  vec2 n = toM*inversesqrt(r2+1.0);
  float e = clamp(pr*2.0 + f, 0.0, 1.0);

  vec2 acc = vec2(0.0);
  // blown outward and swirled around the cursor
  acc += n*f*1.25*uStrength*uSpread*light;
  acc += vec2(-n.y, n.x)*f*1.45*uStrength*uSpread*(0.4+sd);
  // carried by the liquid wake
  acc += (wake*42.0*uSpread*light - vel)*e*0.08;
  // curl-noise turbulence where the material is disturbed (and during the intro)
  vec2 q = p*0.0095 + vec2(uTime*0.35, -uTime*0.2);
  vec2 c = curl(q) + 0.5*curl(q*2.3 + 7.1);
  acc += c*(e*1.2*uSpread + uIntro*1.5)*uTurb*light;
  // elastic return home
  float k = (0.006 + 0.02*uReturn)*(0.65+sd*0.7)*(1.0 - uIntro*0.75);
  acc -= off*k;

  vel += acc*uDt;
  vel *= pow(mix(0.93, 0.955, e), uDt);
  off += vel*uDt;
  if(dot(off,off) < 0.0004 && dot(vel,vel) < 0.0004){ off = vec2(0.0); vel = vec2(0.0); }
  o = vec4(off, vel);
}
`,Sm=`#version 300 es
precision highp float;
layout(location=0) in vec2 aCorner;
uniform sampler2D uState;
uniform sampler2D uHome;
uniform sampler2D uCol;
uniform sampler2D uFlow;
uniform int uTexW;
uniform vec4 uRect;
uniform vec2 uRes;
uniform vec2 uMouse;
uniform float uRadius;
uniform float uPresence;
uniform float uDot;
uniform float uTime;
uniform float uAmbient;
uniform float uStreak;
uniform float uIntroVis;
uniform float uRest;
uniform vec3 uTint;
out vec2 vLocal;
out float vHalfLen;
out float vR;
out vec3 vCol;
out float vA;
${Yf}
${Gh}
void main(){
  int id = gl_InstanceID;
  ivec2 ij = ivec2(id % uTexW, id / uTexW);
  vec4 hm = texelFetch(uHome, ij, 0);
  vec4 st = texelFetch(uState, ij, 0);
  vec4 cl = texelFetch(uCol, ij, 0);

  float wakeE = texture(uFlow, hm.xy).b;
  float E = energyAt(hm.xy*uRect.zw, uMouse, uRadius, uPresence, wakeE);
  float lift = smoothstep(0.35, 2.5, length(st.xy));
  float a = max(max(E, lift), max(uRest, 1.0-uIntroVis));
  if(a < 0.01 || hm.w <= 0.0){ gl_Position = vec4(2.0,2.0,2.0,1.0); return; }

  vec2 base = uRect.xy + hm.xy*uRect.zw + st.xy;
  base -= ambientField(hm.xy, uTime)*10.0*uAmbient;

  vec2 v = st.zw;
  float sp = length(v);
  vec2 dir = sp > 0.001 ? v/sp : vec2(1.0,0.0);
  float r = uDot*hm.w*0.5;
  float len = min(sp*1.25, 18.0)*uStreak;
  vec2 lc = aCorner*vec2(r + len*0.5 + 1.0, r + 1.0);
  vec2 center = base - dir*len*0.5;
  vec2 world = center + dir*lc.x + vec2(-dir.y, dir.x)*lc.y;

  vec2 clip = world/uRes*2.0-1.0;
  gl_Position = vec4(clip.x, -clip.y, 0.0, 1.0);
  vLocal = lc;
  vHalfLen = len*0.5;
  vR = r;
  vec3 col = cl.rgb*uTint;
  // fast material catches the light, like liquid metal
  float glint = smoothstep(2.0, 16.0, sp);
  col = mix(col, min(col*1.18 + vec3(0.07,0.075,0.085), vec3(1.0)), glint*0.75);
  vCol = col;
  vA = a;
}
`,Tm=`#version 300 es
precision highp float;
in vec2 vLocal;
in float vHalfLen;
in float vR;
in vec3 vCol;
in float vA;
uniform float uDpr;
out vec4 o;
void main(){
  vec2 q = vec2(max(abs(vLocal.x)-vHalfLen, 0.0), vLocal.y);
  float d = length(q) - vR;
  float a = clamp(0.5 - d*uDpr, 0.0, 1.0);
  // comet tail: the trailing end thins out
  float tail = vHalfLen > 0.0 ? clamp(0.5 - vLocal.x/(2.0*(vHalfLen+vR)), 0.0, 1.0) : 0.0;
  a *= 1.0 - tail*0.55;
  a *= vA;
  if(a < 0.004) discard;
  o = vec4(vCol*a, a);
}
`,Em=`#version 300 es
precision highp float;
out vec4 o;
uniform sampler2D uImg;
uniform sampler2D uFlow;
uniform vec2 uRes;
uniform float uDpr;
uniform vec4 uRect;
uniform vec3 uBg;
uniform vec3 uImgBg;
uniform vec2 uMouse;
uniform float uPresence;
uniform float uRadius;
uniform float uStrength;
uniform float uTime;
uniform float uAmbient;
uniform float uIntroVis;
uniform float uRest;
uniform float uAlphaMode;
${Yf}
${Gh}
void main(){
  vec2 px = vec2(gl_FragCoord.x, uRes.y*uDpr - gl_FragCoord.y)/uDpr;
  vec2 size = uRect.zw;
  vec2 uv = (px-uRect.xy)/size;
  if(uv.x < -0.05 || uv.x > 1.05 || uv.y < -0.12 || uv.y > 1.12){ o = vec4(0.0); return; }
  vec4 fl = texture(uFlow, clamp(uv,0.0,1.0));
  vec2 vel = (fl.rg*255.0-128.0)/127.0;
  vec2 rp = uv*size;
  vec2 toM = rp - uMouse;
  float fall = exp(-dot(toM,toM)/(uRadius*uRadius))*uPresence;
  vec2 disp = -toM*0.22*uStrength*fall - vel*10.0*uStrength;
  disp += ambientField(uv, uTime)*10.0*uAmbient;
  vec2 suv = uv + disp/size;
  vec4 tx = texture(uImg, suv);
  vec3 col = tx.rgb;
  vec2 e = smoothstep(vec2(0.0), vec2(0.004,0.01), suv) * smoothstep(vec2(1.0), vec2(0.996,0.99), suv);
  // cut-out images carry their own alpha; flat-background images are keyed by colour
  float key = uAlphaMode > 0.5 ? tx.a : smoothstep(0.012, 0.05, length(col-uImgBg));
  float k = key*e.x*e.y;
  float E = energyAt(rp, uMouse, uRadius, uPresence, fl.b);
  float show = (1.0 - max(E, uRest))*uIntroVis;
  vec3 mat = uAlphaMode > 0.5 ? col : col*(uBg/max(uImgBg, vec3(0.001)));
  // premultiplied: empty areas stay transparent so the page (and the backdrop word) shows through
  float A = k*show;
  o = vec4(mat*A, A);
}
`;function gi(S,s,h){const o=i((T,H)=>{const K=S.createShader(T);if(S.shaderSource(K,H),S.compileShader(K),!S.getShaderParameter(K,S.COMPILE_STATUS)){const lt=S.getShaderInfoLog(K);throw S.deleteShader(K),new Error("Shader: "+lt)}return K},"make"),E=S.createProgram(),_=o(S.VERTEX_SHADER,s),O=o(S.FRAGMENT_SHADER,h);if(S.attachShader(E,_),S.attachShader(E,O),S.bindAttribLocation(E,0,"aPos"),S.linkProgram(E),S.deleteShader(_),S.deleteShader(O),!S.getProgramParameter(E,S.LINK_STATUS))throw new Error("Link: "+S.getProgramInfoLog(E));const Y={},z=S.getProgramParameter(E,S.ACTIVE_UNIFORMS);for(let T=0;T<z;T++){const H=S.getActiveUniform(E,T);H&&(Y[H.name]=S.getUniformLocation(E,H.name))}return{p:E,u:Y}}i(gi,"compile");const kl=1024,xm=270,Gf=class Gf{constructor(s,h,o){X(this,"lastImage",null);X(this,"onFirstFrame",null);X(this,"canvas");X(this,"gl");X(this,"opts");X(this,"onFail");X(this,"lowPower",!1);X(this,"stateFormat",{internal:0,type:0});X(this,"pFlow");X(this,"pSim");X(this,"pPart");X(this,"pImg");X(this,"vao");X(this,"quad");X(this,"imgTex",null);X(this,"flowTex",[]);X(this,"flowFb",[]);X(this,"flowW",256);X(this,"flowH",104);X(this,"flowIdx",0);X(this,"flowDirty",!1);X(this,"homeTex",null);X(this,"colTex",null);X(this,"stateTex",[]);X(this,"stateFb",[]);X(this,"stateIdx",0);X(this,"texH",1);X(this,"n",0);X(this,"gridW",1);X(this,"stateDirty",!1);X(this,"alphaMode",!1);X(this,"imgBg",[.918,.914,.906]);X(this,"flowing",0);X(this,"aspect",2.5);X(this,"rect",{x:0,y:0,w:1,h:1});X(this,"cssW",1);X(this,"cssH",1);X(this,"target",{x:-9999,y:-9999});X(this,"mouse",{x:-9999,y:-9999});X(this,"prevMouse",{x:-9999,y:-9999});X(this,"mvel",{x:0,y:0});X(this,"presence",0);X(this,"presenceTarget",0);X(this,"burst",0);X(this,"lastInput",-1e9);X(this,"settle",1e9);X(this,"touchRelease",0);X(this,"time",0);X(this,"introT",1);X(this,"raf",0);X(this,"running",!1);X(this,"last",0);X(this,"skip",0);X(this,"visible",!0);X(this,"ready",!1);X(this,"destroyed",!1);X(this,"lost",!1);X(this,"onLost",i(s=>{s.preventDefault(),this.lost=!0,this.stop(),this.onFail()},"onLost"));X(this,"homeData",null);X(this,"frame",i(s=>{if(!this.running)return;const h=s-this.last,o=xt(h/16.6667,.25,3);this.last=s;const E=this.opts,_=E.ambient&&!E.reducedMotion;this.touchRelease&&s>this.touchRelease&&(this.presenceTarget=0,this.touchRelease=0);const O=1-Math.pow(1-.22,o);this.mouse.x+=(this.target.x-this.mouse.x)*O,this.mouse.y+=(this.target.y-this.mouse.y)*O;const Y=(this.mouse.x-this.prevMouse.x)/o,z=(this.mouse.y-this.prevMouse.y)/o;this.prevMouse.x=this.mouse.x,this.prevMouse.y=this.mouse.y,this.mvel.x+=(xt(Y,-60,60)-this.mvel.x)*.5,this.mvel.y+=(xt(z,-60,60)-this.mvel.y)*.5;const T=this.presenceTarget>this.presence?.1:.035;this.presence+=(this.presenceTarget-this.presence)*(1-Math.pow(1-T,o)),this.presence<.001&&this.presenceTarget===0&&(this.presence=0);const H=this.presenceTarget>this.flowing?.05:.018;this.flowing+=(this.presenceTarget-this.flowing)*(1-Math.pow(1-H,o)),this.flowing<.002&&this.presenceTarget===0&&(this.flowing=0),this.introT<1&&(this.introT=Math.min(1,this.introT+Math.min(h,100)/3200));const K=this.presence>.001||this.burst>0||(E.ambient&&!E.reducedMotion);if(this.settle=K?0:this.settle+o,!(K||this.settle<xm||this.introT<1)){if(this.flowDirty&&this.clearFlow(),this.stateDirty&&this.clearState(),!_){this.draw(),this.firstFrame(),this.stop();return}if(this.raf=requestAnimationFrame(this.frame),this.skip++%2)return;this.time+=o*2*E.speed/60,this.draw(),this.firstFrame();return}this.raf=requestAnimationFrame(this.frame),this.time+=o*E.speed*(1+this.flowing*1.6)/60,this.stepFlow(o),this.stepSim(o),this.burst=Math.max(0,this.burst-.1*o),this.draw(),this.firstFrame()},"frame"));this.canvas=s,this.opts=h,this.onFail=o;const E=s.getContext("webgl2",{alpha:!0,antialias:!1,depth:!1,stencil:!1,premultipliedAlpha:!0,preserveDrawingBuffer:!1,powerPreference:"high-performance"});if(!E)throw new Error("WebGL2 unavailable");this.gl=E;const _=navigator;this.lowPower=(_.hardwareConcurrency||8)<=4||(_.deviceMemory||8)<=4||window.matchMedia("(pointer: coarse)").matches,this.initGL(),s.addEventListener("webglcontextlost",this.onLost,!1)}initGL(){const s=this.gl,h=s.getExtension("EXT_color_buffer_float"),o=h?null:s.getExtension("EXT_color_buffer_half_float");if(!h&&!o)throw new Error("No float render targets");this.stateFormat={internal:s.RGBA16F,type:s.HALF_FLOAT},this.pFlow=gi(s,Hf,bm),this.pSim=gi(s,Hf,pm),this.pPart=gi(s,Sm,Tm),this.pImg=gi(s,Hf,Em),this.vao=s.createVertexArray(),s.bindVertexArray(this.vao),this.quad=s.createBuffer(),s.bindBuffer(s.ARRAY_BUFFER,this.quad),s.bufferData(s.ARRAY_BUFFER,new Float32Array([-1,-1,1,-1,-1,1,1,1]),s.STATIC_DRAW),s.enableVertexAttribArray(0),s.vertexAttribPointer(0,2,s.FLOAT,!1,0,0);for(let E=0;E<2;E++){const _=this.makeTex(s.RGBA8,this.flowW,this.flowH,s.RGBA,s.UNSIGNED_BYTE,null,s.LINEAR),O=s.createFramebuffer();s.bindFramebuffer(s.FRAMEBUFFER,O),s.framebufferTexture2D(s.FRAMEBUFFER,s.COLOR_ATTACHMENT0,s.TEXTURE_2D,_,0),this.flowTex.push(_),this.flowFb.push(O)}this.clearFlow()}makeTex(s,h,o,E,_,O,Y){const z=this.gl,T=z.createTexture();return z.bindTexture(z.TEXTURE_2D,T),z.pixelStorei(z.UNPACK_ALIGNMENT,1),z.texImage2D(z.TEXTURE_2D,0,s,h,o,0,E,_,O),z.texParameteri(z.TEXTURE_2D,z.TEXTURE_MIN_FILTER,Y),z.texParameteri(z.TEXTURE_2D,z.TEXTURE_MAG_FILTER,Y),z.texParameteri(z.TEXTURE_2D,z.TEXTURE_WRAP_S,z.CLAMP_TO_EDGE),z.texParameteri(z.TEXTURE_2D,z.TEXTURE_WRAP_T,z.CLAMP_TO_EDGE),T}clearFlow(){const s=this.gl;for(const h of this.flowFb)s.bindFramebuffer(s.FRAMEBUFFER,h),s.viewport(0,0,this.flowW,this.flowH),s.clearColor(128/255,128/255,0,1),s.clear(s.COLOR_BUFFER_BIT);s.bindFramebuffer(s.FRAMEBUFFER,null),this.flowDirty=!1}clearState(){const s=this.gl;for(const h of this.stateFb)s.bindFramebuffer(s.FRAMEBUFFER,h),s.viewport(0,0,kl,this.texH),s.clearColor(0,0,0,0),s.clear(s.COLOR_BUFFER_BIT);s.bindFramebuffer(s.FRAMEBUFFER,null),this.stateDirty=!1}setImage(s){if(this.destroyed||this.lost)return;const h=this.gl;this.lastImage=s,this.aspect=s.naturalWidth/s.naturalHeight||2.5,this.flowH=Math.max(32,Math.round(this.flowW/this.aspect));for(const o of this.flowTex)h.bindTexture(h.TEXTURE_2D,o),h.texImage2D(h.TEXTURE_2D,0,h.RGBA8,this.flowW,this.flowH,0,h.RGBA,h.UNSIGNED_BYTE,null);this.clearFlow(),this.imgTex&&h.deleteTexture(this.imgTex),this.imgTex=h.createTexture(),h.bindTexture(h.TEXTURE_2D,this.imgTex),h.pixelStorei(h.UNPACK_FLIP_Y_WEBGL,!1),h.texImage2D(h.TEXTURE_2D,0,h.RGBA,h.RGBA,h.UNSIGNED_BYTE,s),h.generateMipmap(h.TEXTURE_2D),h.texParameteri(h.TEXTURE_2D,h.TEXTURE_MIN_FILTER,h.LINEAR_MIPMAP_LINEAR),h.texParameteri(h.TEXTURE_2D,h.TEXTURE_MAG_FILTER,h.LINEAR),h.texParameteri(h.TEXTURE_2D,h.TEXTURE_WRAP_S,h.CLAMP_TO_EDGE),h.texParameteri(h.TEXTURE_2D,h.TEXTURE_WRAP_T,h.CLAMP_TO_EDGE),this.buildParticles(s),this.ready=!0,this.opts.intro&&!this.opts.reducedMotion&&this.startIntro(),this.wake()}buildParticles(s){const h=this.gl,o=this.lowPower?640:1e3,E=Math.round(o*Math.sqrt(xt(this.opts.density,.15,2))),_=Math.max(1,Math.round(E/this.aspect));this.gridW=E;let O;try{const F=document.createElement("canvas");F.width=E,F.height=_;const ct=F.getContext("2d",{willReadFrequently:!0});ct.imageSmoothingQuality="high",ct.drawImage(s,0,0,E,_),O=ct.getImageData(0,0,E,_).data}catch{throw new Error("Image is not CORS-readable")}let Y=0;for(let F=3;F<O.length;F+=4*7)O[F]<250&&Y++;if(this.alphaMode=Y>O.length/(4*7)/20,this.opts.imgBg)this.imgBg=this.opts.imgBg;else if(!this.alphaMode){let F=0,ct=0,nt=0,Lt=0;const Dt=Math.max(2,Math.round(E*.012));for(const[$,P]of[[0,0],[E-Dt,0],[0,_-Dt],[E-Dt,_-Dt]])for(let W=P;W<P+Dt;W++)for(let bt=$;bt<$+Dt;bt++){const Qt=(W*E+bt)*4;F+=O[Qt],ct+=O[Qt+1],nt+=O[Qt+2],Lt++}this.imgBg=[F/Lt/255,ct/Lt/255,nt/Lt/255]}const z=[],T=[];for(let F=0;F<_;F++)for(let ct=0;ct<E;ct++){const nt=(F*E+ct)*4,Lt=O[nt]/255,Dt=O[nt+1]/255,$=O[nt+2]/255;if(this.alphaMode){const bt=O[nt+3]/255;if(bt<.12||bt<.5&&Math.random()>bt*2)continue}else{const bt=Math.hypot(Lt-this.imgBg[0],Dt-this.imgBg[1],$-this.imgBg[2]);if(bt<.03||bt<.07&&Math.random()>(bt-.03)/.04)continue}const P=(Math.random()-.5)*.6,W=(Math.random()-.5)*.6;z.push((ct+.5+P)/E,(F+.5+W)/_,Math.random(),.85+Math.random()*.3),T.push(O[nt],O[nt+1],O[nt+2],255)}const H=z.length/4;this.n=H,this.texH=Math.max(1,Math.ceil(H/kl));const K=kl*this.texH,lt=new Float32Array(K*4);lt.set(z);const _t=new Uint8Array(K*4);_t.set(T);for(const F of[this.homeTex,this.colTex,...this.stateTex])F&&h.deleteTexture(F);for(const F of this.stateFb)h.deleteFramebuffer(F);this.stateTex=[],this.stateFb=[],this.homeTex=this.makeTex(h.RGBA32F,kl,this.texH,h.RGBA,h.FLOAT,lt,h.NEAREST),this.colTex=this.makeTex(h.RGBA8,kl,this.texH,h.RGBA,h.UNSIGNED_BYTE,_t,h.NEAREST);for(let F=0;F<2;F++){const ct=this.makeTex(this.stateFormat.internal,kl,this.texH,h.RGBA,this.stateFormat.type,null,h.NEAREST),nt=h.createFramebuffer();if(h.bindFramebuffer(h.FRAMEBUFFER,nt),h.framebufferTexture2D(h.FRAMEBUFFER,h.COLOR_ATTACHMENT0,h.TEXTURE_2D,ct,0),h.checkFramebufferStatus(h.FRAMEBUFFER)!==h.FRAMEBUFFER_COMPLETE)throw new Error("Float FBO incomplete");this.stateTex.push(ct),this.stateFb.push(nt)}h.bindFramebuffer(h.FRAMEBUFFER,null),this.homeData=lt,this.clearState()}startIntro(){const s=this.gl,h=this.homeData;if(!h)return;const o=this.rect.w>10?this.rect.w:1300,E=kl*this.texH,_=new Float32Array(E*4);for(let O=0;O<this.n;O++){const Y=h[O*4]-.5,z=h[O*4+1]-.5,T=h[O*4+2],H=(Math.random()-.5)*.9+(Y>0?0:Math.PI),K=o*(.04+Math.random()*.14)*(.5+Math.abs(Y)*1.4);_[O*4]=Math.cos(H)*K+Y*o*.25*T,_[O*4+1]=Math.sin(H)*K*.55+z*120*(Math.random()-.5)}s.bindTexture(s.TEXTURE_2D,this.stateTex[this.stateIdx]),s.texImage2D(s.TEXTURE_2D,0,this.stateFormat.internal,kl,this.texH,0,s.RGBA,s.FLOAT,_),this.introT=0,this.stateDirty=!0}setOptions(s){const h=s.density!==void 0&&s.density!==this.opts.density||s.imgBg!==void 0&&String(s.imgBg)!==String(this.opts.imgBg);if(this.opts={...this.opts,...s},h&&this.ready&&this.lastImage)try{this.buildParticles(this.lastImage)}catch{this.onFail()}this.wake()}resize(s,h,o){this.cssW=Math.max(1,s),this.cssH=Math.max(1,h),this.rect=o;let E=Math.min(window.devicePixelRatio||1,this.lowPower?1.5:2);this.cssW*this.cssH*E*E>45e5&&(E=Math.max(1,Math.sqrt(45e5/(this.cssW*this.cssH))));const O=Math.round(this.cssW*E),Y=Math.round(this.cssH*E);(this.canvas.width!==O||this.canvas.height!==Y)&&(this.canvas.width=O,this.canvas.height=Y),this.draw()}setVisible(s){this.visible=s,s?this.wake():this.stop()}pointer(s,h,o,E){if(!this.opts.interactive||this.lost)return;const _=performance.now();if(s==="leave"||s==="up"){E?this.touchRelease=_+260:this.presenceTarget=0,this.wake();return}(this.mouse.x<-9e3||s==="down")&&(this.mouse.x=this.prevMouse.x=h,this.mouse.y=this.prevMouse.y=o),this.target.x=h,this.target.y=o,this.presenceTarget=1,this.lastInput=_,this.settle=0,s==="down"&&(this.burst=1,this.touchRelease=0),this.wake()}wake(){if(!this.ready||this.running||!this.visible||this.destroyed||this.lost){this.ready&&!this.running&&this.draw();return}this.running=!0,this.last=performance.now(),this.raf=requestAnimationFrame(this.frame)}stop(){this.running=!1,cancelAnimationFrame(this.raf)}radiusPx(){return this.opts.radius*xt(this.rect.w/1400,.6,1.25)}strength(){return this.opts.strength*(this.opts.reducedMotion?.5:1)}firstFrame(){if(this.onFirstFrame){const s=this.onFirstFrame;this.onFirstFrame=null,requestAnimationFrame(()=>s())}}stepFlow(s){const h=this.gl,o=this.pFlow,E=1-this.flowIdx;h.bindFramebuffer(h.FRAMEBUFFER,this.flowFb[E]),h.viewport(0,0,this.flowW,this.flowH),h.disable(h.BLEND),h.useProgram(o.p),h.bindVertexArray(this.vao),h.activeTexture(h.TEXTURE0),h.bindTexture(h.TEXTURE_2D,this.flowTex[this.flowIdx]),h.uniform1i(o.u.uPrev,0),h.uniform2f(o.u.uTexel,1/this.flowW,1/this.flowH);const _=this.rect;h.uniform2f(o.u.uMouse,(this.mouse.x-_.x)/_.w,(this.mouse.y-_.y)/_.h);const ambVx=Math.sin(this.time*1.8)*0.22,ambVy=Math.cos(this.time*2.2)*0.18;h.uniform2f(o.u.uVel,xt(this.mvel.x/40+ambVx,-1,1),xt(this.mvel.y/40+ambVy,-1,1)),h.uniform2f(o.u.uSize,_.w,_.h),h.uniform1f(o.u.uRadius,this.radiusPx()),h.uniform1f(o.u.uPresence,Math.max(this.presence,this.burst)),h.uniform1f(o.u.uDecay,Math.pow(.968,s)),h.drawArrays(h.TRIANGLE_STRIP,0,4),this.flowIdx=E,this.flowDirty=!0}stepSim(s){if(!this.n)return;const h=this.gl,o=this.pSim,E=this.opts,_=this.rect,O=1-this.stateIdx,Y=E.reducedMotion||!E.intro?0:Math.pow(1-this.introT,2);h.bindFramebuffer(h.FRAMEBUFFER,this.stateFb[O]),h.viewport(0,0,kl,this.texH),h.disable(h.BLEND),h.useProgram(o.p),h.bindVertexArray(this.vao),h.activeTexture(h.TEXTURE0),h.bindTexture(h.TEXTURE_2D,this.stateTex[this.stateIdx]),h.uniform1i(o.u.uState,0),h.activeTexture(h.TEXTURE1),h.bindTexture(h.TEXTURE_2D,this.homeTex),h.uniform1i(o.u.uHome,1),h.activeTexture(h.TEXTURE2),h.bindTexture(h.TEXTURE_2D,this.flowTex[this.flowIdx]),h.uniform1i(o.u.uFlow,2),h.uniform2f(o.u.uSize,_.w,_.h),h.uniform2f(o.u.uMouse,this.mouse.x-_.x,this.mouse.y-_.y),h.uniform1f(o.u.uRadius,this.radiusPx()),h.uniform1f(o.u.uPresence,Math.max(this.presence,this.burst)),h.uniform1f(o.u.uStrength,this.strength()*(1+this.burst*1.5)),h.uniform1f(o.u.uSpread,xt(E.spread,0,2)),h.uniform1f(o.u.uTurb,xt(E.turbulence,0,2)*(E.reducedMotion?.3:1)),h.uniform1f(o.u.uReturn,xt(E.returnSpeed,0,2)),h.uniform1f(o.u.uTime,this.time),h.uniform1f(o.u.uDt,s),h.uniform1f(o.u.uIntro,Y),h.drawArrays(h.TRIANGLE_STRIP,0,4),this.stateIdx=O,this.stateDirty=!0}draw(){if(!this.ready||this.lost||this.destroyed)return;const s=this.gl,h=this.opts,o=this.rect,E=h.reducedMotion,_=E||!h.intro?1:Math.pow(this.introT,2.2),Y=(h.ambient&&!E?xt(h.ambientIntensity,0,2):0)+this.flowing*.85*xt(h.liquid,0,2)*(E?.3:1),z=h.restStyle==="particles"?1:0,[T,H,K]=h.bg,[lt,_t,F]=this.imgBg,ct=this.canvas.width/this.cssW,nt=Math.max(this.presence,this.burst),Lt=this.mouse.x-o.x,Dt=this.mouse.y-o.y;s.bindFramebuffer(s.FRAMEBUFFER,null),s.viewport(0,0,this.canvas.width,this.canvas.height),s.bindVertexArray(this.vao),s.disable(s.BLEND);const $=this.pImg;if(s.useProgram($.p),s.activeTexture(s.TEXTURE0),s.bindTexture(s.TEXTURE_2D,this.imgTex),s.uniform1i($.u.uImg,0),s.activeTexture(s.TEXTURE1),s.bindTexture(s.TEXTURE_2D,this.flowTex[this.flowIdx]),s.uniform1i($.u.uFlow,1),s.uniform2f($.u.uRes,this.cssW,this.cssH),s.uniform1f($.u.uDpr,ct),s.uniform4f($.u.uRect,o.x,o.y,o.w,o.h),s.uniform3f($.u.uBg,T,H,K),s.uniform3f($.u.uImgBg,lt,_t,F),s.uniform2f($.u.uMouse,Lt,Dt),s.uniform1f($.u.uPresence,nt),s.uniform1f($.u.uRadius,this.radiusPx()),s.uniform1f($.u.uStrength,this.strength()),s.uniform1f($.u.uTime,this.time),s.uniform1f($.u.uAmbient,Y),s.uniform1f($.u.uIntroVis,_),s.uniform1f($.u.uRest,z),s.uniform1f($.u.uAlphaMode,this.alphaMode?1:0),s.drawArrays(s.TRIANGLE_STRIP,0,4),!this.n||!this.stateDirty&&z===0&&_>=1)return;const P=this.pPart;s.useProgram(P.p),s.enable(s.BLEND),s.blendFunc(s.ONE,s.ONE_MINUS_SRC_ALPHA),s.activeTexture(s.TEXTURE0),s.bindTexture(s.TEXTURE_2D,this.stateTex[this.stateIdx]),s.uniform1i(P.u.uState,0),s.activeTexture(s.TEXTURE1),s.bindTexture(s.TEXTURE_2D,this.homeTex),s.uniform1i(P.u.uHome,1),s.activeTexture(s.TEXTURE2),s.bindTexture(s.TEXTURE_2D,this.colTex),s.uniform1i(P.u.uCol,2),s.activeTexture(s.TEXTURE3),s.bindTexture(s.TEXTURE_2D,this.flowTex[this.flowIdx]),s.uniform1i(P.u.uFlow,3),s.uniform1i(P.u.uTexW,kl),s.uniform4f(P.u.uRect,o.x,o.y,o.w,o.h),s.uniform2f(P.u.uRes,this.cssW,this.cssH),s.uniform2f(P.u.uMouse,Lt,Dt),s.uniform1f(P.u.uRadius,this.radiusPx()),s.uniform1f(P.u.uPresence,nt);const W=o.w/this.gridW;s.uniform1f(P.u.uDot,W*1.45*xt(h.dotSize,.2,3)),s.uniform1f(P.u.uTime,this.time),s.uniform1f(P.u.uAmbient,Y),s.uniform1f(P.u.uStreak,(h.streaks?1:0)*(.45+.55*_)),s.uniform1f(P.u.uIntroVis,_),s.uniform1f(P.u.uRest,z),s.uniform1f(P.u.uDpr,ct),this.alphaMode?s.uniform3f(P.u.uTint,1,1,1):s.uniform3f(P.u.uTint,T/Math.max(lt,.001),H/Math.max(_t,.001),K/Math.max(F,.001)),s.drawArraysInstanced(s.TRIANGLE_STRIP,0,4,this.n),s.disable(s.BLEND)}destroy(){var h;this.destroyed=!0,this.stop(),this.canvas.removeEventListener("webglcontextlost",this.onLost);const s=this.gl;if(!this.lost){s.deleteBuffer(this.quad),s.deleteVertexArray(this.vao);for(const o of[...this.flowTex,...this.stateTex,this.imgTex,this.homeTex,this.colTex])o&&s.deleteTexture(o);for(const o of[...this.flowFb,...this.stateFb])s.deleteFramebuffer(o);for(const o of[this.pFlow,this.pSim,this.pPart,this.pImg])o&&s.deleteProgram(o.p)}(h=s.getExtension("WEBGL_lose_context"))==null||h.loseContext()}};i(Gf,"LiquidEngine");

function Am(S,s,h,o,E){const _=Math.max(80,o.bottom-o.top);let O,Y;S>=1024?(O=S*0.80*E.scale,Y=_*0.85*E.scale):S>=640?(O=S*0.95*E.scale,Y=_*0.88*E.scale):h<1.3?(O=S*0.80*E.mobileScale,Y=_*0.80*E.mobileScale):(O=S*1.35*E.mobileScale,Y=_*0.92*E.mobileScale),O/h>Y&&(O=Y*h);const z=O/h,T=S*(.5+E.x/100),H=(o.top+o.bottom)/2+s*(E.y/100);return{x:T-O/2,y:H-z/2,w:O,h:z}}i(Am,"computeRect");

window.LiquidEngine = Gf;
window.computeSculptureRect = Am;
window.hexToRgb = Hh;
window.clamp = xt;
