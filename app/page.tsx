"use client";

import { useState } from "react";

type Role = "Managing Partner" | "Lawyer" | "Client Admin" | "Client User";

const roleAccess: Record<Role,string[]> = {
  "Managing Partner":["Overview","Matters","Legal Requests","Documents","Calendar","Retainer","Communications","Finance","Reports","Security"],
  "Lawyer":["Overview","Matters","Legal Requests","Documents","Calendar","Retainer","Communications","Security"],
  "Client Admin":["Overview","Matters","Legal Requests","Documents","Calendar","Retainer","Communications","Finance"],
  "Client User":["Overview","Matters","Legal Requests","Documents","Calendar","Communications"]
};

const matters=[["AC-MAT-2026-0001","Perjanjian Vendor & Commercial Review","IN PROGRESS"],["AC-MAT-2026-0002","Ketenagakerjaan & Industrial Relations","WAITING CLIENT"],["AC-MAT-2026-0003","Dispute Prevention & Legal Advisory","OPEN"]];
const requests=[["AC-REQ-0017","Review draft vendor agreement","HIGH"],["AC-REQ-0016","Legal opinion on employee warning letter","NORMAL"],["AC-REQ-0015","Contract clause clarification","COMPLETED"]];

function Login({onLogin}:{onLogin:(role:Role)=>void}){
  const [role,setRole]=useState<Role>("Managing Partner");
  return <main className="loginShell"><section className="loginCard"><div className="loginBrand"><div className="mark">AC</div><div><b>ARKANA CIVIEL</b><span>LEGAL MANAGEMENT</span></div></div><small className="eyebrow">V4 UAT DEMO</small><h1>Secure workspace</h1><p className="loginCopy">Choose a demo role to test the access boundaries and service workflows. No real authentication or client data is used.</p><div className="roleGrid">{(Object.keys(roleAccess) as Role[]).map(r=><button type="button" key={r} className={role===r?"roleCard active":"roleCard"} onClick={()=>setRole(r)}><b>{r}</b><span>{roleAccess[r].length} demo modules</span></button>)}</div><button className="primary" type="button" onClick={()=>onLogin(role)}>Enter {role} Demo →</button><small className="demoNote">Demo only · Production authentication is provided by the V4 backend, not this static preview.</small></section></main>;
}

function Overview({setActive,role}:{setActive:(v:string)=>void;role:Role}){
 return <><div className="metrics">{[["12","Active Matters"],["7","Open Requests"],["4","SLA At Risk"],["3","Upcoming Hearings"]].map(([n,l])=><div className="metric" key={l}><strong>{n}</strong><span>{l}</span><small>Mock UAT data</small></div>)}</div>
 <div className="grid"><section className="card"><div className="head"><div><small>MATTER WORKSPACE</small><h2>Active Legal Matters</h2></div><button onClick={()=>setActive("Matters")}>View all →</button></div>{matters.map(m=><button className="row rowButton" key={m[0]} onClick={()=>setActive("Matters")}><div className="icon">§</div><div><small>{m[0]}</small><b>{m[1]}</b><span>Corporate & Commercial · Partner supervised</span></div><em className={m[2]==="WAITING CLIENT"?"warn":"ok"}>{m[2]}</em></button>)}</section>
 <section className="card"><div className="head"><div><small>SERVICE DELIVERY</small><h2>Legal Requests</h2></div><button onClick={()=>setActive("Legal Requests")}>Open queue →</button></div>{requests.map(r=><button className="row compact rowButton" key={r[0]} onClick={()=>setActive("Legal Requests")}><div><small>{r[0]}</small><b>{r[1]}</b><span>Deadline · 19 Sep 2026</span></div><em className={r[2]==="HIGH"?"high":r[2]==="COMPLETED"?"ok":""}>{r[2]}</em></button>)}</section></div>
 <div className="grid three"><section className="card"><small>RETAINER</small><h2>Operational Legal Partner</h2><div className="big">62%</div><span>Monthly support utilization</span><div className="bar"><i></i></div></section><section className="card"><small>SECURITY</small><h2>Production Baseline</h2><ul><li>2FA challenge enabled</li><li>Secure session cookies</li><li>Audit logging active</li><li>Request ID tracing</li></ul></section><section className="card"><small>AUTOMATION</small><h2>Workflow Engine</h2><div className="event"><b>SLA breach escalation</b><span>Partner → Managing Partner</span></div><div className="event"><b>Client action reminder</b><span>H-24 · H-4 · overdue</span></div></section></div><div className="uatStrip"><b>UAT role:</b> {role}<span>Access is simulated locally for this demo.</span></div></>;
}

function Section({title,role}:{title:string;role:Role}){
 const [selected,setSelected]=useState<string|null>(null);
 const data:Record<string,[string,string][]>={Matters:[["AC-MAT-2026-0001","Perjanjian Vendor & Commercial Review"],["AC-MAT-2026-0002","Ketenagakerjaan & Industrial Relations"],["AC-MAT-2026-0003","Dispute Prevention & Legal Advisory"]],"Legal Requests":[["AC-REQ-0017","Review draft vendor agreement"],["AC-REQ-0016","Legal opinion on employee warning letter"],["AC-REQ-0015","Contract clause clarification"]],Documents:[["DOC-204","Vendor Agreement — Final Review"],["DOC-198","Employee Handbook — Partner Review"],["DOC-191","Legal Opinion — Client Visible"]],Calendar:[["19 Sep 2026","Hearing · District Court"],["22 Sep 2026","Client meeting · Retainer"],["25 Sep 2026","Contract review deadline"]],Retainer:[["RET-2026-014","Corporate Legal Retainer · Active"],["62%","Monthly utilization"],["30 Sep 2026","Next renewal review"]],Communications:[["THREAD-88","Client message · Vendor agreement"],["THREAD-84","Partner note · Employment matter"],["THREAD-79","Client approval · Legal opinion"]],Finance:[["INV-2026-041","Outstanding · IDR 18,500,000"],["INV-2026-038","Paid · IDR 25,000,000"],["EXP-2026-019","Matter expense · IDR 1,250,000"]],Reports:[["September 2026","Executive legal operations snapshot"],["SLA","4 requests require attention"],["Portfolio","12 active matters across 8 clients"]],Security:[["2FA","Enabled for privileged users"],["Sessions","Secure, organization-scoped"],["Audit","Sensitive actions recorded"]]};
 const rows=data[title]||[];
 return <section className="card wide"><div className="moduleHead"><div><small>V4 UAT MODULE · {role.toUpperCase()}</small><h2>{title}</h2></div>{title==="Legal Requests"&&<button className="primary smallButton" onClick={()=>setSelected("NEW REQUEST")}>+ New Request</button>}</div><p className="moduleIntro">Interactive demo view using mock data. Click a row to inspect a simulated workflow state.</p>{rows.map(([a,b])=><button className="moduleRow moduleButton" key={a} onClick={()=>setSelected(a)}><b>{a}</b><span>{b}</span><em>DEMO</em></button>)}{selected&&<div className="detailPanel"><div><small>UAT DETAIL</small><h3>{selected}</h3><p>Simulated detail state for {title}. Production data, API calls, uploads and approvals are intentionally disabled in this demo.</p></div><button onClick={()=>setSelected(null)}>Close</button></div>}</section>;
}

export default function Home(){
 const [role,setRole]=useState<Role|null>(null);
 const [active,setActive]=useState("Overview");
 if(!role)return <Login onLogin={setRole}/>;
 const allowed=roleAccess[role];
 const go=(v:string)=>setActive(allowed.includes(v)?v:"Overview");
 return <main><aside><div className="brand"><div className="mark">AC</div><div><b>ARKANA CIVIEL</b><span>LEGAL MANAGEMENT</span></div></div><nav aria-label="Main navigation">{allowed.map(x=><button aria-current={active===x?"page":undefined} key={x} className={active===x?"sel":""} onClick={()=>go(x)}>{x}</button>)}</nav><div className="secure">🔒<b> Secure workspace</b><small>{role}<br/>Organization-scoped demo access</small><button className="signout" onClick={()=>setRole(null)}>Sign out</button></div></aside>
 <section className="page"><header><div><small>INTERNAL COMMAND CENTER · V4 UAT DEMO</small><h1>{active}</h1><p>Arkana Civiel Law Firm — legal operations at a glance.</p></div><div className="user"><span className="dot"></span> {role} <b>AC</b></div></header>{active==="Overview"?<Overview setActive={go} role={role}/>:<Section title={active} role={role}/>}<footer><span>ARKANA CIVIEL · V4 UAT DEMO</span><span>Mock data only · No production client data</span></footer></section></main>;
}
