<?php
session_start();
require_once "../config/database.php";

// Vérif auth — role admin
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Infos admin connecté
$stmt = $pdo->prepare("SELECT CONCAT(prenom,' ',nom) AS name FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$adminRow = $stmt->fetch(PDO::FETCH_ASSOC);
$userName = $adminRow['name'] ?? ($_SESSION['user_name'] ?? 'Admin');
$parts    = explode(' ', $userName);
$userIni  = strtoupper(substr($parts[0], 0, 1) . substr($parts[1] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CM2E — Administration</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<style>
:root {
  --red:#E31E24;--red-d:#B91419;--red-l:#FF5257;--red-bg:#FFF5F5;--red-bdr:#FED7D7;--red-soft:rgba(227,30,36,.09);
  --bg:#F8F9FC;--sur:#FFFFFF;--sur2:#F2F4F8;--sur3:#E8EBF2;
  --bdr:#E4E7EF;--bdr2:#CFD4E3;
  --txt:#0F1624;--txt2:#5A6478;--txt3:#9AA3B7;
  --green:#059669;--gbg:#ECFDF5;--gold:#D97706;--ybg:#FFFBEB;--blue:#2563EB;--bbg:#EFF6FF;
  --r:10px;--r2:7px;--r3:5px;
  --sh:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);
  --sh2:0 4px 24px rgba(0,0,0,.09),0 1px 4px rgba(0,0,0,.05);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--txt);min-height:100vh;overflow-x:hidden;font-size:14px}
button{cursor:pointer;border:none;font-family:inherit}
input,select,textarea{font-family:inherit}
::-webkit-scrollbar{width:4px;height:4px}
::-webkit-scrollbar-track{background:var(--sur2)}
::-webkit-scrollbar-thumb{background:var(--bdr2);border-radius:4px}
::-webkit-scrollbar-thumb:hover{background:var(--red)}
.app{display:flex;min-height:100vh}

/* SIDEBAR */
.sidebar{width:248px;background:var(--sur);border-right:1px solid var(--bdr);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100}
.sb-brand{padding:18px 18px 14px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;gap:12px}
.sb-logo{width:38px;height:38px;background:var(--red);border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:11px;color:white;flex-shrink:0;line-height:1.1;text-align:center}
.sb-brand-name{font-weight:800;font-size:13px;letter-spacing:-.2px}
.sb-brand-sub{font-size:10px;color:var(--txt3);font-weight:500;text-transform:uppercase;letter-spacing:.07em}
.sb-user{margin:12px 12px 0;padding:10px 12px;background:var(--red-bg);border:1px solid var(--red-bdr);border-radius:var(--r);display:flex;align-items:center;gap:9px}
.sb-av{width:30px;height:30px;background:var(--red);border-radius:7px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;color:white;flex-shrink:0}
.sb-uname{font-weight:700;font-size:12px}
.sb-urole{font-size:10px;color:var(--red);font-weight:600}
.sb-dot{margin-left:auto;width:7px;height:7px;background:var(--green);border-radius:50%;box-shadow:0 0 0 2px var(--gbg);animation:blink 2s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.sb-nav{flex:1;overflow-y:auto;padding:10px 8px}
.sb-section{margin-bottom:18px}
.sb-label{font-size:9.5px;font-weight:700;letter-spacing:.1em;color:var(--txt3);text-transform:uppercase;padding:0 8px;margin-bottom:5px}
.nav-item{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:var(--r2);color:var(--txt2);font-size:12.5px;font-weight:500;cursor:pointer;transition:all .15s;margin-bottom:1px;border:1px solid transparent;position:relative}
.nav-item:hover{background:var(--sur2);color:var(--txt)}
.nav-item.active{background:var(--red-soft);color:var(--red);border-color:rgba(227,30,36,.15);font-weight:600}
.nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:55%;background:var(--red);border-radius:0 3px 3px 0}
.nav-ico{font-size:14px;width:17px;text-align:center;flex-shrink:0}
.nav-badge{margin-left:auto;background:var(--red);color:white;font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:20px}
.nav-dot{margin-left:auto;width:7px;height:7px;background:var(--green);border-radius:50%;animation:blink 2s infinite}
.sb-foot{padding:10px 8px;border-top:1px solid var(--bdr)}
.sb-reset{width:100%;padding:9px;background:linear-gradient(135deg,var(--red),var(--red-d));color:white;border-radius:var(--r);font-weight:700;font-size:11.5px;letter-spacing:.03em;display:flex;align-items:center;justify-content:center;gap:6px;transition:all .2s;box-shadow:0 2px 8px rgba(227,30,36,.28);margin-bottom:6px}
.sb-reset:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(227,30,36,.38)}
.sb-logout{width:100%;padding:7px;background:transparent;border:1px solid var(--bdr);color:var(--txt3);border-radius:var(--r2);font-size:11.5px;display:flex;align-items:center;justify-content:center;gap:5px;transition:all .15s}
.sb-logout:hover{border-color:var(--red);color:var(--red)}

/* MAIN */
.main{margin-left:248px;flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.93);backdrop-filter:blur(12px);border-bottom:1px solid var(--bdr);padding:0 24px;height:56px;display:flex;align-items:center;justify-content:space-between}
.tb-badge{background:var(--red);color:white;border-radius:5px;padding:2px 8px;font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase}
.tb-title{font-weight:700;font-size:16px;letter-spacing:-.3px}
.tb-right{display:flex;align-items:center;gap:8px}
.tb-conn{background:var(--gbg);border:1px solid rgba(5,150,105,.2);color:var(--green);border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;display:flex;align-items:center;gap:4px}
.tb-conn::before{content:'';width:5px;height:5px;background:var(--green);border-radius:50%;animation:blink 2s infinite}
.tb-btn{padding:6px 14px;background:var(--sur2);border:1px solid var(--bdr);color:var(--txt2);border-radius:var(--r2);font-size:12px;font-weight:500;display:flex;align-items:center;gap:5px;transition:all .15s}
.tb-btn:hover{border-color:var(--red);color:var(--red);background:var(--red-bg)}
.tb-btn.prim{background:var(--red);border-color:var(--red);color:white;font-weight:600;box-shadow:0 2px 7px rgba(227,30,36,.22)}
.tb-btn.prim:hover{background:var(--red-d)}
.notif{width:34px;height:34px;background:var(--sur2);border:1px solid var(--bdr);color:var(--txt2);border-radius:var(--r2);font-size:15px;display:flex;align-items:center;justify-content:center;position:relative;transition:all .15s}
.notif:hover{border-color:var(--red)}
.notif-dot{position:absolute;top:5px;right:5px;width:6px;height:6px;background:var(--red);border-radius:50%;border:2px solid white}

/* SECTIONS */
.sec{display:none;padding:22px 24px}
.sec.active{display:block}
.sec-hd{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px}
.sec-title{font-weight:800;font-size:18px;letter-spacing:-.3px}
.sec-sub{font-size:12px;color:var(--txt3);margin-top:2px}

/* STAT CARDS */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}
.sc{background:var(--sur);border:1px solid var(--bdr);border-radius:var(--r);padding:16px 18px;position:relative;overflow:hidden;transition:all .2s;box-shadow:var(--sh)}
.sc:hover{transform:translateY(-2px);box-shadow:var(--sh2);border-color:var(--red-bdr)}
.sc-accent{position:absolute;top:0;left:0;right:0;height:3px}
.sc-r .sc-accent{background:var(--red)}.sc-g .sc-accent{background:var(--green)}.sc-o .sc-accent{background:var(--gold)}.sc-b .sc-accent{background:var(--blue)}
.sc-ico{font-size:22px;margin-bottom:8px}.sc-val{font-weight:800;font-size:28px;letter-spacing:-.5px;line-height:1}.sc-lbl{font-size:11.5px;color:var(--txt2);margin-top:3px;font-weight:500}
.sc-badge{position:absolute;top:14px;right:12px;font-size:10.5px;font-weight:700;padding:2px 6px;border-radius:20px}
.up{background:var(--gbg);color:var(--green)}.dn{background:var(--red-bg);color:var(--red)}

/* CARD */
.card{background:var(--sur);border:1px solid var(--bdr);border-radius:var(--r);box-shadow:var(--sh);overflow:hidden}
.card-hd{padding:12px 18px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;background:var(--sur2)}
.card-title{font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px}
.card-body{padding:18px}

/* TABLE */
.tbl{width:100%;border-collapse:collapse}
.tbl th{padding:8px 12px;font-size:10.5px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt3);text-align:left;border-bottom:1px solid var(--bdr);white-space:nowrap;background:var(--sur2)}
.tbl td{padding:8px 12px;border-bottom:1px solid var(--bdr);vertical-align:middle;font-size:12.5px}
.tbl tr:last-child td{border-bottom:none}.tbl tr:hover td{background:var(--sur2)}
.tbl th.dc,.tbl td.dc{text-align:center;min-width:100px}

/* CHIPS */
.chip{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;white-space:nowrap}
.chip-F{background:rgba(227,30,36,.1);color:#C0181D;border:1px solid rgba(227,30,36,.22)}
.chip-V{background:rgba(217,119,6,.1);color:#B45309;border:1px solid rgba(217,119,6,.22)}
.chip-C{background:rgba(37,99,235,.1);color:#1D4ED8;border:1px solid rgba(37,99,235,.22)}
.chip-R{background:rgba(5,150,105,.1);color:#047857;border:1px solid rgba(5,150,105,.22)}
.chip-A{background:rgba(156,163,175,.15);color:var(--txt3);border:1px solid rgba(156,163,175,.3)}
.chip-e{background:transparent;color:var(--txt3);border:1px dashed var(--bdr2)}
.chip-prog{background:var(--bbg);color:var(--blue);border:1px solid rgba(37,99,235,.2)}
.chip-done{background:var(--gbg);color:var(--green);border:1px solid rgba(5,150,105,.2)}
.chip-wait{background:var(--ybg);color:var(--gold);border:1px solid rgba(217,119,6,.2)}

/* AVA */
.ava{width:28px;height:28px;border-radius:7px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:10.5px;font-weight:700}
.ava-lg{width:40px;height:40px;border-radius:10px;font-size:13px}
.ava-xl{width:52px;height:52px;border-radius:12px;font-size:16px;font-weight:800}

/* SCORES */
.two-col{display:grid;grid-template-columns:1fr 290px;gap:16px}
.podium{display:flex;align-items:flex-end;justify-content:center;gap:12px;margin-bottom:22px}
.pp{display:flex;flex-direction:column;align-items:center;gap:6px;flex:1;max-width:120px}
.pa-w{position:relative}
.pp-av{border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:white}
.pp-crown{position:absolute;bottom:-4px;right:-4px;font-size:14px}
.pp-name{font-size:11.5px;font-weight:600;text-align:center}
.pp-pts{font-weight:800;font-size:19px}
.pp-bar{width:100%;border-radius:5px 5px 0 0;display:flex;align-items:center;justify-content:center;font-size:9.5px;font-weight:700;color:white}
.pp1 .pp-bar{height:68px;background:linear-gradient(180deg,#F59E0B,#D97706)}.pp2 .pp-bar{height:48px;background:linear-gradient(180deg,#94A3B8,#64748B)}.pp3 .pp-bar{height:36px;background:linear-gradient(180deg,#D97706,#92400E)}
.pp1 .pp-av{width:46px;height:46px;font-size:14px;box-shadow:0 0 14px rgba(217,119,6,.35);background:linear-gradient(135deg,#FCD34D,#D97706)}
.pp2 .pp-av{width:40px;height:40px;font-size:13px;background:linear-gradient(135deg,#CBD5E1,#64748B)}
.pp3 .pp-av{width:36px;height:36px;font-size:12px;background:linear-gradient(135deg,#FDE68A,#D97706)}
.pp1 .pp-pts{color:var(--gold)}.pp2 .pp-pts{color:var(--txt3)}.pp3 .pp-pts{color:#B45309}
.score-row{display:flex;align-items:center;gap:9px;padding:7px 0;border-bottom:1px solid var(--bdr)}
.score-row:last-child{border-bottom:none}
.s-rank{width:20px;height:20px;border-radius:50%;background:var(--sur2);border:1px solid var(--bdr);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:var(--txt3);flex-shrink:0}
.s-name{flex:1;font-size:12.5px;font-weight:500}
.s-bar-w{width:70px}.s-bar-bg{height:4px;background:var(--sur3);border-radius:2px;overflow:hidden}.s-bar{height:100%;border-radius:2px;transition:width .5s ease}
.s-pts{font-weight:800;font-size:14px;color:var(--gold);white-space:nowrap;font-family:'DM Mono',monospace}

/* TIMER */
.timer-cd{background:var(--sur2);border:1px solid var(--bdr);border-radius:var(--r);padding:14px;text-align:center}
.timer-lbl{font-size:9.5px;color:var(--txt3);letter-spacing:.08em;text-transform:uppercase;margin-bottom:8px}
.timer-digs{display:flex;gap:5px;justify-content:center;align-items:center}
.t-unit{display:flex;flex-direction:column;align-items:center;gap:2px}
.t-num{font-family:'DM Mono',monospace;font-size:24px;font-weight:500;background:white;border:1px solid var(--bdr);border-radius:5px;padding:4px 8px;min-width:44px;text-align:center;color:var(--red);box-shadow:var(--sh)}
.t-sep{font-size:18px;color:var(--txt3);margin-bottom:8px;font-weight:300}.t-ulbl{font-size:9px;color:var(--txt3);text-transform:uppercase;letter-spacing:.05em}
.pbar-bg{height:5px;background:var(--sur3);border-radius:3px;overflow:hidden;margin:4px 0}
.pbar-fill{height:100%;background:linear-gradient(90deg,var(--red),var(--red-l));border-radius:3px}

/* METRO GRID */
.metro-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.metro-card{background:var(--sur);border:1px solid var(--bdr);border-radius:var(--r);padding:16px;cursor:pointer;transition:all .2s;box-shadow:var(--sh)}
.metro-card:hover{border-color:var(--red);transform:translateY(-2px);box-shadow:var(--sh2)}
.mc-top{display:flex;align-items:center;gap:9px;margin-bottom:12px}
.mc-info strong{font-size:12.5px;font-weight:700;display:block}.mc-info span{font-size:10.5px;color:var(--txt3)}
.mc-badge{margin-left:auto;font-size:14px}
.mc-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:5px}
.mc-stat{background:var(--sur2);border:1px solid var(--bdr);border-radius:6px;padding:6px;text-align:center}
.mc-stat-val{font-weight:800;font-size:15px;font-family:'DM Mono',monospace}.mc-stat-key{font-size:9px;color:var(--txt3);text-transform:uppercase;letter-spacing:.05em;margin-top:1px}

/* PROJ GRID */
.proj-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.proj-card{background:var(--sur);border:1px solid var(--bdr);border-radius:var(--r);padding:16px;cursor:pointer;transition:all .2s;box-shadow:var(--sh);position:relative;overflow:hidden}
.proj-card::before{content:'';position:absolute;top:0;left:0;width:3px;height:100%;background:var(--red)}
.proj-card:hover{border-color:var(--red);transform:translateY(-2px);box-shadow:var(--sh2)}
.pj-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.pj-name{font-weight:700;font-size:13px;margin-bottom:4px}
.pj-tasks{display:grid;grid-template-columns:1fr 1fr;gap:4px;margin-bottom:10px}
.pt{display:flex;align-items:center;gap:4px;padding:3px 7px;border-radius:4px;font-size:10.5px;font-weight:500}
.pt.done{text-decoration:line-through;opacity:.5}
.pb-bg{height:3px;background:var(--sur3);border-radius:2px;overflow:hidden;margin:3px 0}.pb-fill{height:100%;border-radius:2px}
.pb-lbl{display:flex;justify-content:space-between;font-size:10px;color:var(--txt3)}
.pj-assign{display:flex;align-items:center;gap:6px;margin-top:8px;padding-top:8px;border-top:1px solid var(--bdr);font-size:11.5px;color:var(--txt2)}

/* FORMS */
.fg{margin-bottom:13px}.fl{font-size:11.5px;font-weight:600;color:var(--txt2);margin-bottom:5px;display:block}
.fi{width:100%;background:var(--sur2);border:1px solid var(--bdr);color:var(--txt);border-radius:var(--r2);padding:8px 12px;font-size:13px;outline:none;transition:all .15s}
.fi:focus{border-color:var(--red);background:white;box-shadow:0 0 0 3px var(--red-soft)}
.fr2{display:grid;grid-template-columns:1fr 1fr;gap:10px}.fr3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
.fb{padding:8px 16px;border-radius:var(--r2);font-size:12.5px;font-weight:600;transition:all .15s;display:inline-flex;align-items:center;gap:5px}
.fb.prim{background:var(--red);color:white;box-shadow:0 2px 7px rgba(227,30,36,.22)}.fb.prim:hover{background:var(--red-d);transform:translateY(-1px)}
.fb.sec{background:var(--sur2);border:1px solid var(--bdr);color:var(--txt)}.fb.sec:hover{border-color:var(--red);color:var(--red)}
.fb.danger{background:#FEF2F2;border:1px solid #FCA5A5;color:#DC2626}.fb.danger:hover{background:#DC2626;color:white}

/* PARAMS */
.params-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.param-card{background:var(--sur);border:1px solid var(--bdr);border-radius:var(--r);padding:20px;box-shadow:var(--sh)}
.pc-title{font-weight:700;font-size:13px;margin-bottom:15px;display:flex;align-items:center;gap:6px}

/* ML-BTN */
.ml-btn{background:none;border:none;color:var(--red);font-size:11.5px;cursor:pointer;font-family:inherit;font-weight:600;transition:color .15s}
.ml-btn:hover{color:var(--red-d)}

/* MODALS */
.overlay{display:none;position:fixed;inset:0;z-index:200;background:rgba(10,18,30,.35);backdrop-filter:blur(5px);align-items:center;justify-content:center;padding:16px}
.overlay.open{display:flex;animation:fadeIn .15s}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.modal{background:var(--sur);border:1px solid var(--bdr);border-radius:14px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;animation:slideUp .2s ease;box-shadow:var(--sh2)}
.modal-sm{max-width:460px}.modal-md{max-width:560px}.modal-lg{max-width:720px}
.modal-hd{padding:18px 20px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;gap:10px;position:sticky;top:0;background:var(--sur);z-index:1}
.modal-title{font-weight:800;font-size:16px;letter-spacing:-.2px}.modal-sub{font-size:11px;color:var(--txt3);margin-top:1px}
.modal-x{margin-left:auto;width:28px;height:28px;background:var(--sur2);border:1px solid var(--bdr);color:var(--txt2);border-radius:6px;font-size:13px;display:flex;align-items:center;justify-content:center;transition:all .15s}
.modal-x:hover{border-color:var(--red);color:var(--red)}
.modal-body{padding:20px}.modal-foot{padding:12px 20px;border-top:1px solid var(--bdr);display:flex;justify-content:flex-end;gap:8px;background:var(--sur2)}
.modal-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:16px}
.ms{background:var(--sur2);border:1px solid var(--bdr);border-radius:7px;padding:10px;text-align:center}
.ms-val{font-weight:800;font-size:18px;font-family:'DM Mono',monospace}.ms-key{font-size:9.5px;color:var(--txt3);text-transform:uppercase;letter-spacing:.05em;margin-top:2px}
.modal-sec{font-weight:700;font-size:11px;color:var(--txt3);text-transform:uppercase;letter-spacing:.07em;margin:14px 0 8px}
.pli{background:var(--sur2);border:1px solid var(--bdr);border-radius:7px;padding:9px 11px;display:flex;align-items:center;gap:9px;margin-bottom:5px}
.pli-dot{width:7px;height:7px;border-radius:2px;flex-shrink:0}.pli-name{flex:1;font-size:12.5px;font-weight:500}
.abs-chip{background:var(--red-bg);border:1px solid var(--red-bdr);color:var(--red);font-size:11px;padding:3px 9px;border-radius:20px}
.tasks-prev{background:var(--sur2);border:1px solid var(--bdr);border-radius:var(--r2);padding:11px;margin-top:10px}
.tp-title{font-size:10.5px;color:var(--txt2);font-weight:700;letter-spacing:.05em;margin-bottom:7px;text-transform:uppercase}
.tasks-4{display:grid;grid-template-columns:1fr 1fr;gap:5px}
.t4{display:flex;align-items:center;gap:5px;padding:5px 9px;border-radius:4px;font-size:11.5px;font-weight:600}
.t4-n{width:16px;height:16px;border-radius:50%;background:rgba(0,0,0,.1);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;flex-shrink:0}

/* CHAMPION */
.champ-ov{display:none;position:fixed;inset:0;z-index:300;background:rgba(10,18,30,.65);backdrop-filter:blur(6px);align-items:center;justify-content:center}
.champ-ov.open{display:flex}
.conf-c{position:absolute;inset:0;pointer-events:none;overflow:hidden}
.conf-p{position:absolute;top:-20px;animation:cfall linear infinite}
@keyframes cfall{to{transform:translateY(110vh) rotate(720deg)}}
.champ-card{background:white;border:2px solid var(--red);border-radius:18px;padding:32px;text-align:center;max-width:440px;width:100%;position:relative;animation:champPop .3s cubic-bezier(.17,.67,.38,1.4);box-shadow:0 20px 60px rgba(227,30,36,.18)}
@keyframes champPop{from{transform:scale(.55);opacity:0}to{transform:scale(1);opacity:1}}
.champ-crown{font-size:50px;margin-bottom:6px;animation:bounce 1.1s infinite}
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
.champ-lbl{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--red);margin-bottom:6px}
.champ-name{font-weight:800;font-size:30px;letter-spacing:-.5px;background:linear-gradient(135deg,#F59E0B,#D97706);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:3px}
.champ-sub{font-size:12.5px;color:var(--txt2);margin-bottom:3px}
.champ-score{font-weight:800;font-size:40px;color:var(--gold);margin-bottom:5px;font-family:'DM Mono',monospace}
.champ-q{font-size:11.5px;color:var(--txt3);margin-bottom:20px}
.champ-btns{display:flex;gap:8px;justify-content:center}
.champ-pub{padding:10px 18px;border-radius:8px;background:linear-gradient(135deg,var(--red),var(--red-d));color:white;font-weight:700;font-size:12px;text-transform:uppercase;transition:all .15s;box-shadow:0 2px 10px rgba(227,30,36,.28)}
.champ-pub:hover{transform:translateY(-1px)}
.champ-cancel{padding:10px 18px;border-radius:8px;background:var(--sur2);border:1px solid var(--bdr);color:var(--txt2);font-weight:600;font-size:12px;transition:all .15s}
.champ-cancel:hover{border-color:var(--red);color:var(--red)}
.champ-note{font-size:11px;color:var(--txt3);margin-top:12px}

/* CONFIRM CARD */
.conf-card{background:white;border:1px solid var(--bdr);border-radius:14px;width:100%;max-width:400px;padding:26px;text-align:center;animation:slideUp .2s ease;box-shadow:var(--sh2)}
.conf-icon{font-size:40px;margin-bottom:12px}.conf-title{font-weight:800;font-size:18px;letter-spacing:-.2px;margin-bottom:7px}
.conf-desc{font-size:12.5px;color:var(--txt2);line-height:1.65;margin-bottom:14px}
.conf-warn{background:var(--red-bg);border:1px solid var(--red-bdr);border-radius:6px;padding:9px 11px;font-size:11.5px;color:var(--red);margin-bottom:18px;text-align:left}
.conf-btns{display:flex;gap:7px;justify-content:center}

/* HORAIRES */
.hor-row{background:var(--sur2);border:1px solid var(--bdr);border-radius:var(--r2);padding:10px 14px;display:flex;align-items:center;gap:12px;margin-bottom:6px}
.hor-name{flex:1;font-weight:600;font-size:12.5px}
.hor-time{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--txt2)}
.hor-inp{width:85px;background:white;border:1px solid var(--bdr);border-radius:5px;padding:4px 7px;font-size:12px;outline:none;transition:all .15s}
.hor-inp:focus{border-color:var(--red)}

/* POINTAGE */
.pres-ok{background:var(--gbg);color:var(--green)}.pres-late{background:var(--ybg);color:var(--gold)}.pres-abs{background:var(--red-bg);color:var(--red)}

/* LOG */
.log-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--bdr);font-size:12px}
.log-row:last-child{border-bottom:none}.log-ico{font-size:15px;flex-shrink:0}.log-msg{flex:1}
.log-time{font-size:10.5px;color:var(--txt3);white-space:nowrap;font-family:'DM Mono',monospace}
.log-type{padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700}
.log-ok{background:var(--gbg);color:var(--green)}.log-warn{background:var(--ybg);color:var(--gold)}.log-err{background:var(--red-bg);color:var(--red)}

/* EXPORT CARDS */
.exp-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px}
.exp-card{background:var(--sur);border:1px solid var(--bdr);border-radius:var(--r);padding:16px;display:flex;flex-direction:column;align-items:flex-start;gap:8px;box-shadow:var(--sh);transition:all .2s}
.exp-card:hover{border-color:var(--red);box-shadow:var(--sh2)}
.exp-ico{font-size:24px}.exp-title{font-weight:700;font-size:13px}.exp-desc{font-size:11.5px;color:var(--txt2);line-height:1.5}

/* TOAST */
.toasts{position:fixed;bottom:18px;right:18px;z-index:500;display:flex;flex-direction:column;gap:6px}
.toast{background:white;border:1px solid var(--bdr);border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;font-size:12.5px;box-shadow:var(--sh2);animation:toastIn .22s ease;min-width:250px}
@keyframes toastIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
.toast.ok{border-left:3px solid var(--green)}.toast.info{border-left:3px solid var(--blue)}.toast.warn{border-left:3px solid var(--gold)}.toast.err{border-left:3px solid var(--red)}

/* LOADING */
.loading{display:flex;align-items:center;justify-content:center;padding:40px;color:var(--txt3);font-size:13px;gap:8px}
.spin{width:18px;height:18px;border:2px solid var(--bdr);border-top-color:var(--red);border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<div class="app">

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">CM<br>2E</div>
    <div>
      <div class="sb-brand-name">CM2E Admin</div>
      <div class="sb-brand-sub">Centre de Métrologie</div>
    </div>
  </div>
  <div class="sb-user">
    <div class="sb-av"><?= $userIni ?></div>
    <div>
      <div class="sb-uname"><?= htmlspecialchars($userName) ?></div>
      <div class="sb-urole">Gérant / Administrateur</div>
    </div>
    <div class="sb-dot"></div>
  </div>
  <nav class="sb-nav">
    <div class="sb-section">
      <div class="sb-label">Vue générale</div>
      <div class="nav-item active" onclick="showSec('dashboard',this)"><span class="nav-ico">📊</span>Tableau de bord <span class="nav-dot"></span></div>
      <div class="nav-item" onclick="showSec('planning',this)"><span class="nav-ico">📅</span>Planning semaine</div>
      <div class="nav-item" onclick="showSec('scores',this)"><span class="nav-ico">🏆</span>Scores &amp; Classement <span class="nav-badge">T<?= ceil(date('n')/3) ?></span></div>
    </div>
    <div class="sb-section">
      <div class="sb-label">Gestion</div>
      <div class="nav-item" onclick="showSec('metrologues',this)"><span class="nav-ico">🔧</span>Métrologues</div>
      <div class="nav-item" onclick="showSec('projets',this)"><span class="nav-ico">📁</span>Projets</div>
      <div class="nav-item" onclick="showSec('pointages',this)"><span class="nav-ico">🕐</span>Pointages</div>
    </div>
    <div class="sb-section">
      <div class="sb-label">Administration</div>
      <div class="nav-item" onclick="showSec('donnees',this)"><span class="nav-ico">💾</span>Données</div>
      <div class="nav-item" onclick="showSec('parametres',this)"><span class="nav-ico">⚙️</span>Paramètres</div>
      <div class="nav-item" onclick="showSec('securite',this)"><span class="nav-ico">🔐</span>Sécurité</div>
      <div class="nav-item" onclick="showSec('systeme',this)"><span class="nav-ico">🖥️</span>Système</div>
    </div>
  </nav>
  <div class="sb-foot">
    <button class="sb-reset" onclick="openOv('ov-reset')">🔄 Démarrer les scores</button>
    <button class="sb-logout" onclick="location.href='../auth/logout.php'">🚪 Déconnexion</button>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:10px">
      <div class="tb-badge">ADMIN</div>
      <div class="tb-title" id="page-title">Tableau de bord</div>
    </div>
    <div class="tb-right">
      <div class="tb-conn">Plateforme métrologue connectée</div>
      <button class="tb-btn" onclick="openChamp()">🏅 Résultat trimestriel</button>
      <button class="tb-btn prim" onclick="openAddProj()">＋ Nouveau projet</button>
    </div>
  </div>

  <!-- ── DASHBOARD ── -->
  <div class="sec active" id="sec-dashboard">
    <div class="stats-row" id="stats-row">
      <div class="sc sc-r"><div class="sc-accent"></div><div class="sc-ico">📁</div><div class="sc-val" id="st-projets">—</div><div class="sc-lbl">Projets actifs</div></div>
      <div class="sc sc-g"><div class="sc-accent"></div><div class="sc-ico">🔧</div><div class="sc-val" id="st-metros">—</div><div class="sc-lbl">Métrologues actifs</div></div>
      <div class="sc sc-o"><div class="sc-accent"></div><div class="sc-ico">✅</div><div class="sc-val" id="st-taches">—</div><div class="sc-lbl">Tâches terminées</div></div>
    </div>
    <div class="two-col">
  <!-- Carte gauche : Top 3 -->
  <div class="card">
    <div class="card-hd"><div class="card-title">🏆 Top 3 — Trimestre en cours</div></div>
    <div class="card-body" id="dash-podium">
      <div id="podium-content" style="display:none"></div>
      <div id="podium-timer" style="text-align:center;padding:20px">
        <div style="font-size:13px;color:var(--txt2);margin-bottom:10px">Résultat disponible dans</div>
        <div class="timer-digs" style="justify-content:center">
          <div class="t-unit"><div class="t-num" id="td3">—</div><div class="t-ulbl">Jours</div></div>
          <div class="t-sep">:</div>
          <div class="t-unit"><div class="t-num" id="th3">—</div><div class="t-ulbl">H</div></div>
          <div class="t-sep">:</div>
          <div class="t-unit"><div class="t-num" id="tm3">—</div><div class="t-ulbl">Min</div></div>
        </div>
      </div>
    </div>
  </div>
  <!-- Carte droite : Fin de trimestre -->
  <div class="card">
    <div class="card-hd"><div class="card-title">⏱ Fin de trimestre</div></div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
      <div>
        <div style="display:flex;justify-content:space-between;font-size:11.5px;color:var(--txt2);margin-bottom:4px">
          <span>Trimestre <?= ceil(date('n')/3) ?> — <?= date('Y') ?></span>
          <span style="color:var(--red);font-weight:700" id="trim-pct">68%</span>
        </div>
        <div class="pbar-bg"><div class="pbar-fill" id="trim-bar" style="width:68%"></div></div>
      </div>
      <div style="background:var(--ybg);border:1px solid rgba(217,119,6,.2);border-radius:8px;padding:10px;font-size:12px" id="leader-badge">🏅 Chargement...</div>
      <button class="fb prim" style="justify-content:center" onclick="openChamp()">🎉 Résultat trimestriel</button>
    </div>
  </div>
</div>

  <!-- ── PLANNING ── -->
  <div class="sec" id="sec-planning">
    <div class="sec-hd">
      <div><div class="sec-title">📅 Planning Hebdomadaire</div><div class="sec-sub">Suivi des tâches par métrologue</div></div>
    </div>
    <div class="card">
      <div style="overflow-x:auto">
        <table class="tbl"><thead><tr>
          <th style="min-width:155px">Métrologue</th>
          <th class="dc">Lun</th><th class="dc">Mar</th><th class="dc">Mer</th><th class="dc">Jeu</th><th class="dc">Ven</th>
          <th>Score</th><th>Action</th>
        </tr></thead><tbody id="plan-full"><tr><td colspan="8" class="loading"><div class="spin"></div></td></tr></tbody></table>
      </div>
    </div>
  </div>

  <!-- ── SCORES ── -->
  <div class="sec" id="sec-scores">
    <div class="sec-hd">
      <div><div class="sec-title">🏆 Scores &amp; Classement</div><div class="sec-sub">Trimestre <?= ceil(date('n')/3) ?> — <?= date('Y') ?></div></div>
      <div style="display:flex;gap:8px">
        <button class="tb-btn" onclick="openChamp()">🎉 Résultat trimestriel</button>
        <button class="tb-btn prim" onclick="openOv('ov-reset')">🔄 Nouveau trimestre</button>
      </div>
    </div>
    <div class="two-col">
      <div class="card">
        <div class="card-hd"><div class="card-title">🥇 Podium</div></div>
        <div class="card-body" id="scores-podium"><div class="loading"><div class="spin"></div></div></div>
        <div class="card-body" id="scores-list" style="padding-top:0"></div>
      </div>
      <div class="card">
        <div class="card-hd"><div class="card-title">⏱ Compte à rebours</div></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
          <div class="timer-cd">
            <div class="timer-lbl">Fin de trimestre dans</div>
            <div class="timer-digs">
              <div class="t-unit"><div class="t-num" id="td2">—</div><div class="t-ulbl">Jours</div></div>
              <div class="t-sep">:</div>
              <div class="t-unit"><div class="t-num" id="th2">—</div><div class="t-ulbl">H</div></div>
              <div class="t-sep">:</div>
              <div class="t-unit"><div class="t-num" id="tm2">—</div><div class="t-ulbl">Min</div></div>
            </div>
          </div>
          <div>
            <div style="display:flex;justify-content:space-between;font-size:11.5px;color:var(--txt2);margin-bottom:4px"><span>Progression</span><span style="color:var(--red);font-weight:700">68%</span></div>
            <div class="pbar-bg"><div class="pbar-fill" style="width:68%"></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── MÉTROLOGUES ── -->
  <div class="sec" id="sec-metrologues">
    <div class="sec-hd">
      <div><div class="sec-title">🔧 Gestion des Métrologues</div><div class="sec-sub">Classeur TFT/LED associé à chaque métrologue</div></div>
      <button class="tb-btn prim" onclick="openAddMetro()">＋ Nouveau métrologue</button>
    </div>
    <div class="metro-grid" id="metro-grid"><div class="loading" style="grid-column:1/-1"><div class="spin"></div>Chargement...</div></div>
  </div>

  <!-- ── PROJETS ── -->
  <div class="sec" id="sec-projets">
    <div class="sec-hd">
      <div><div class="sec-title">📁 Gestion des Projets</div></div>
      <button class="tb-btn prim" onclick="openAddProj()">＋ Nouveau projet</button>
    </div>
    <div class="proj-grid" id="proj-grid"><div class="loading" style="grid-column:1/-1"><div class="spin"></div>Chargement...</div></div>
  </div>

  <!-- ── POINTAGES ── -->
  <div class="sec" id="sec-pointages">
    <div class="sec-hd">
      <div><div class="sec-title">🕐 Consultation des Pointages</div><div class="sec-sub">Historique des présences</div></div>
      <div style="display:flex;gap:8px">
        <select class="fi" style="width:160px;padding:6px 10px;font-size:12px" id="filter-metro"><option value="">Tous</option></select>
        <input class="fi" type="date" style="width:140px;padding:6px 10px;font-size:12px" id="filter-date"/>
        <button class="tb-btn prim" onclick="loadPointages()">🔍 Filtrer</button>
        <button class="tb-btn" onclick="location.href='api.php?action=export_pointages'">📗 Export CSV</button>
      </div>
    </div>
    <div class="card">
      <div style="overflow-x:auto">
        <table class="tbl"><thead><tr>
          <th>Métrologue</th><th>Date</th><th>Arrivée</th><th>Départ</th><th>Durée</th><th>Statut</th><th>Note</th>
        </tr></thead><tbody id="pointage-body"><tr><td colspan="7" class="loading"><div class="spin"></div></td></tr></tbody></table>
      </div>
    </div>
  </div>

  <!-- ── DONNÉES ── -->
  <div class="sec" id="sec-donnees">
    <div class="sec-hd"><div><div class="sec-title">💾 Gestion des Données</div><div class="sec-sub">Export, sauvegarde et nettoyage</div></div></div>
    <div class="exp-cards">
      <div class="exp-card">
        <div class="exp-ico">📊</div>
        <div class="exp-title">Exporter les pointages</div>
        <div class="exp-desc">CSV complet des présences/absences</div>
        <button class="fb prim" onclick="location.href='api.php?action=export_pointages'">📗 Télécharger CSV</button>
      </div>
      <div class="exp-card">
        <div class="exp-ico">📁</div>
        <div class="exp-title">Exporter les projets</div>
        <div class="exp-desc">CSV : nom projet, métrologue, tâches, dates</div>
        <button class="fb prim" onclick="location.href='api.php?action=export_projets'">📗 Télécharger CSV</button>
      </div>
      <div class="exp-card">
        <div class="exp-ico">💾</div>
        <div class="exp-title">Backup complet (JSON)</div>
        <div class="exp-desc">Sauvegarde totale de la base de données</div>
        <button class="fb prim" onclick="location.href='api.php?action=backup'">⬇️ Télécharger</button>
      </div>
    </div>
    <div class="card">
      <div class="card-hd"><div class="card-title">🗑️ Suppression des anciens enregistrements</div></div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div>
            <div class="fl">Supprimer les pointages antérieurs à</div>
            <div style="display:flex;gap:8px;align-items:center">
              <input class="fi" type="date" id="delete-before" style="flex:1"/>
              <button class="fb danger" onclick="confirmDeleteOld()">🗑️ Supprimer</button>
            </div>
            <div style="font-size:11px;color:var(--txt3);margin-top:6px">⚠️ Un CSV sera généré automatiquement avant suppression</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── PARAMÈTRES ── -->
  <div class="sec" id="sec-parametres">
    <div class="sec-hd"><div><div class="sec-title">⚙️ Paramètres</div><div class="sec-sub">Horaires et configuration du système</div></div></div>
    <div class="params-grid">
      <div class="param-card">
        <div class="pc-title">🕐 Horaires par métrologue</div>
        <div id="horaires-list"><div class="loading"><div class="spin"></div></div></div>
        <button class="fb prim" style="margin-top:8px" onclick="saveHoraires()">💾 Enregistrer les horaires</button>
      </div>
      <div class="param-card">
        <div class="pc-title">🏆 Système de scores</div>
        <div class="fg"><label class="fl">Durée du cycle</label><select class="fi"><option selected>3 mois (trimestriel)</option><option>6 mois</option></select></div>
        <div class="fr2">
          <div class="fg"><label class="fl">Points / tâche complétée</label><input class="fi" type="number" value="50"/></div>
          <div class="fg"><label class="fl">Pénalité / absence</label><input class="fi" type="number" value="-20"/></div>
        </div>
        <div class="fr2">
          <div class="fg"><label class="fl">Bonus leader</label><input class="fi" type="number" value="30"/></div>
          <div class="fg"><label class="fl">Pénalité retard</label><input class="fi" type="number" value="-5"/></div>
        </div>
        <div style="background:var(--sur2);border:1px solid var(--bdr);border-radius:6px;padding:9px;font-size:11.5px;color:var(--txt2);margin-bottom:12px">
          📋 <strong>4 tâches fixes :</strong> Finaliser · Vérifier · Commande · Réception
        </div>
        <button class="fb prim" onclick="toast('ok','✅ Paramètres scores enregistrés')">💾 Enregistrer</button>
      </div>
    </div>
  </div>

  <!-- ── SÉCURITÉ ── -->
  <div class="sec" id="sec-securite">
    <div class="sec-hd"><div><div class="sec-title">🔐 Sécurité</div><div class="sec-sub">Profil admin et journal des actions</div></div></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
      <div class="param-card">
        <div class="pc-title">👤 Modifier le profil admin</div>
        <div class="fg"><label class="fl">Nom complet</label><input class="fi" id="sec-name" value="<?= htmlspecialchars($userName) ?>"/></div>
        <div class="fg"><label class="fl">Email / Identifiant</label><input class="fi" id="sec-email" value="<?= htmlspecialchars($user['user_email'] ?? '') ?>"/></div>
        <button class="fb prim" onclick="saveProfile()">💾 Mettre à jour</button>
      </div>
      <div class="param-card">
        <div class="pc-title">🔑 Changer le mot de passe</div>
        <div class="fg"><label class="fl">Mot de passe actuel</label><input class="fi" type="password" id="sec-old" placeholder="••••••••"/></div>
        <div class="fg"><label class="fl">Nouveau mot de passe</label><input class="fi" type="password" id="sec-new" placeholder="••••••••"/></div>
        <div class="fg"><label class="fl">Confirmer</label><input class="fi" type="password" id="sec-conf" placeholder="••••••••"/></div>
        <button class="fb prim" onclick="changePassword()">🔑 Changer</button>
      </div>
    </div>
    <div class="card">
      <div class="card-hd">
        <div class="card-title">📋 Journal des actions</div>
        <button class="tb-btn" onclick="location.href='api.php?action=export_pointages'">📗 Exporter</button>
      </div>
      <div class="card-body" id="journal-body"><div class="loading"><div class="spin"></div></div></div>
    </div>
  </div>

  <!-- ── SYSTÈME ── -->
  <div class="sec" id="sec-systeme">
    <div class="sec-hd"><div><div class="sec-title">🖥️ Gestion du système</div></div></div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px">
      <div class="card" style="padding:18px"><div style="font-size:22px;margin-bottom:8px">📡</div><div style="font-weight:700;font-size:13px;margin-bottom:4px">Connexion Badge</div><div style="display:flex;align-items:center;gap:6px;font-size:12px"><span style="width:8px;height:8px;background:var(--green);border-radius:50%;display:inline-block"></span><span style="color:var(--green);font-weight:600">Connecté</span></div></div>
      <div class="card" style="padding:18px"><div style="font-size:22px;margin-bottom:8px">📺</div><div style="font-weight:700;font-size:13px;margin-bottom:4px">Écrans TFT</div><div style="display:flex;align-items:center;gap:6px;font-size:12px"><span style="width:8px;height:8px;background:var(--green);border-radius:50%;display:inline-block"></span><span style="color:var(--green);font-weight:600">Actifs</span></div></div>
      <div class="card" style="padding:18px"><div style="font-size:22px;margin-bottom:8px">🗄️</div><div style="font-weight:700;font-size:13px;margin-bottom:4px">Base de données</div><div style="display:flex;align-items:center;gap:6px;font-size:12px"><span style="width:8px;height:8px;background:var(--green);border-radius:50%;display:inline-block"></span><span style="color:var(--green);font-weight:600">En ligne</span></div></div>
    </div>
    <div class="params-grid">
      <div class="param-card">
        <div class="pc-title">🔄 Redémarrage du système</div>
        <div style="font-size:12.5px;color:var(--txt2);margin-bottom:14px;line-height:1.6">Réinitialise la connexion avec les dispositifs matériels (badge, TFT, LEDs). La BDD reste intacte.</div>
        <div style="background:var(--ybg);border:1px solid rgba(217,119,6,.2);border-radius:6px;padding:9px;font-size:11.5px;color:var(--gold);margin-bottom:14px">⚠️ Sessions métrologues interrompues ~30 secondes.</div>
        <button class="fb danger" style="width:100%;justify-content:center" onclick="openOv('ov-restart')">🔄 Redémarrer le système</button>
      </div>
      <div class="param-card">
        <div class="pc-title">📊 Informations système</div>
        <div style="display:flex;flex-direction:column;gap:7px">
          <div style="display:flex;justify-content:space-between;font-size:12px;padding:6px 0;border-bottom:1px solid var(--bdr)"><span style="color:var(--txt2)">Version</span><span style="font-weight:700;font-family:'DM Mono',monospace">v2.1.0</span></div>
          <div style="display:flex;justify-content:space-between;font-size:12px;padding:6px 0;border-bottom:1px solid var(--bdr)"><span style="color:var(--txt2)">Date du serveur</span><span style="font-weight:600"><?= date('d/m/Y H:i') ?></span></div>
          <div style="display:flex;justify-content:space-between;font-size:12px;padding:6px 0"><span style="color:var(--txt2)">PHP</span><span style="font-weight:700;font-family:'DM Mono',monospace"><?= PHP_VERSION ?></span></div>
        </div>
      </div>
    </div>
  </div>
</div><!-- /main -->
</div><!-- /app -->

<!-- MODAL PROFIL MÉTROLOGUE -->
<div class="overlay" id="ov-metro">
  <div class="modal modal-lg">
    <div class="modal-hd">
      <div class="ava ava-xl" id="mm-av" style="color:white">KB</div>
      <div><div class="modal-title" id="mm-name">—</div><div class="modal-sub" id="mm-role">—</div></div>
      <button class="modal-x" onclick="closeOv('ov-metro')">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-stats">
        <div class="ms"><div class="ms-val" style="color:var(--gold)" id="mm-score">—</div><div class="ms-key">Score</div></div>
        <div class="ms"><div class="ms-val" style="color:var(--blue)" id="mm-projs">—</div><div class="ms-key">Projets</div></div>
        <div class="ms"><div class="ms-val" style="color:var(--green)" id="mm-pres">—</div><div class="ms-key">ID</div></div>
        <div class="ms"><div class="ms-val" style="color:var(--red)" id="mm-abs">—</div><div class="ms-key">Absences</div></div>
      </div>
      <div class="modal-sec">🔧 Informations</div>
      <div class="fr3" id="mm-infos"></div>
      <div class="modal-sec">📁 Projets &amp; Tâches</div>
      <div id="mm-projets"></div>
      <div class="modal-sec">📅 Absences (trimestre)</div>
      <div style="display:flex;flex-wrap:wrap;gap:5px" id="mm-absences"></div>
    </div>
    <div class="modal-foot">
      <button class="fb danger" id="btn-del-metro" onclick="deleteMetro()">🗑️ Supprimer</button>
      <button class="fb sec" id="btn-edit-metro" onclick="openEditMetro()">✏️ Modifier</button>
      <button class="fb prim" onclick="closeOv('ov-metro')">Fermer</button>
    </div>
  </div>
</div>

<!-- MODAL AJOUTER/MODIFIER MÉTROLOGUE -->
<div class="overlay" id="ov-addmetro">
  <div class="modal modal-md">
    <div class="modal-hd">
      <div><div class="modal-title" id="metro-modal-title">🔧 Nouveau Métrologue</div><div class="modal-sub">Compte + classeur TFT/LED</div></div>
      <button class="modal-x" onclick="closeOv('ov-addmetro')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="m-id"/>
      <div class="fr2">
        <div class="fg"><label class="fl">Nom complet *</label><input class="fi" id="m-name" placeholder="Prénom Nom"/></div>
        <div class="fg"><label class="fl">Email *</label><input class="fi" type="email" id="m-email" placeholder="prenom.nom@cm2e.tn"/></div>
      </div>
      <div class="fr3">
        <div class="fg"><label class="fl">Téléphone</label><input class="fi" id="m-tel" placeholder="+216 XX XXX XXX"/></div>
        <div class="fg"><label class="fl">Niveau</label><select class="fi" id="m-niveau"><option>Junior</option><option>Intermédiaire</option><option>Senior</option><option>Expert</option></select></div>
        <div class="fg"><label class="fl">Spécialité</label><select class="fi" id="m-spec"><option>Calibration</option><option>Vérification</option><option>Maintenance</option><option>Audit</option></select></div>
      </div>
      <div class="fr2">
        <div class="fg"><label class="fl">Poste / Fonction</label><input class="fi" id="m-poste" placeholder="Métrologie industrielle"/></div>
        <div class="fg">
          <label class="fl">N° Classeur (TFT/LED) *</label>
          <select class="fi" id="m-classeur">
            <option value="">— Sélectionner —</option>
            <option>1</option><option>2</option><option>3</option><option>4</option>
            <option>5</option><option>6</option><option>7</option><option>8</option>
          </select>
          <div style="font-size:10.5px;color:var(--txt3);margin-top:3px">Identifie ce métrologue sur l'écran TFT et active les LEDs</div>
        </div>
      </div>
      <div class="fg" id="pass-field">
        <label class="fl">Mot de passe initial *</label>
        <input class="fi" type="password" id="m-pass" placeholder="••••••••"/>
      </div>
    </div>
    <div class="modal-foot">
      <button class="fb sec" onclick="closeOv('ov-addmetro')">Annuler</button>
      <button class="fb prim" onclick="saveMetro()">✓ Enregistrer</button>
    </div>
  </div>
</div>

<!-- MODAL AJOUTER PROJET -->
<div class="overlay" id="ov-addproj">
  <div class="modal modal-md">
    <div class="modal-hd">
      <div><div class="modal-title">📁 Nouveau Projet</div><div class="modal-sub">4 tâches assignées automatiquement</div></div>
      <button class="modal-x" onclick="closeOv('ov-addproj')">✕</button>
    </div>
    <div class="modal-body">
      <div class="fg"><label class="fl">Titre du projet *</label><input class="fi" id="p-nom" placeholder="Ex: Étalonnage capteurs pression P-12"/></div>
      <div class="fg"><label class="fl">Métrologue assigné *</label><select class="fi" id="p-metro"><option value="">— Sélectionner —</option></select></div>
      <div class="fr2">
        <div class="fg"><label class="fl">Échéance</label><input class="fi" type="date" id="p-deadline"/></div>
        <div class="fg"><label class="fl">Priorité</label><select class="fi" id="p-prio"><option value="normale">Normale</option><option value="haute">Haute</option><option value="urgente">Urgente</option></select></div>
      </div>
      <div class="fg" style="margin-top:11px"><label class="fl">Description</label><textarea class="fi" id="p-desc" rows="2" placeholder="Détails..." style="resize:vertical"></textarea></div>
    </div>
    <div class="modal-foot">
      <button class="fb sec" onclick="closeOv('ov-addproj')">Annuler</button>
      <button class="fb prim" onclick="saveProj()">✓ Créer le projet</button>
    </div>
  </div>
</div>

<!-- CHAMPION -->
<div class="champ-ov" id="ov-champ">
  <div class="conf-c" id="conf-c"></div>
  <div class="champ-card">
    <div class="champ-crown">👑</div>
    <div class="champ-lbl">🎊 Champion du Trimestre 🎊</div>
    <div class="champ-name" id="champ-name">—</div>
    <div class="champ-sub">meilleur score du trimestre</div>
    <div class="champ-score" id="champ-score">—</div>
    <div class="champ-q">Trimestre <?= ceil(date('n')/3) ?> — <?= date('Y') ?></div>
    <div class="champ-btns">
      <button class="champ-pub" onclick="publishChamp()">📢 Publier sur plateforme métrologue</button>
      <button class="champ-cancel" onclick="closeChamp()">✕ Annuler</button>
    </div>
    <div class="champ-note" id="champ-note"></div>
  </div>
</div>

<!-- MODALS CONFIRMATION -->
<div class="overlay" id="ov-reset">
  <div class="conf-card">
    <div class="conf-icon">🔄</div>
    <div class="conf-title">Démarrer un nouveau trimestre ?</div>
    <div class="conf-desc">Tous les scores seront remis à <strong>0</strong>. L'historique est conservé.</div>
    <div class="conf-warn">⚠️ <strong>Action irréversible.</strong> Validez le résultat du trimestre d'abord.</div>
    <div class="conf-btns">
      <button class="fb sec" onclick="closeOv('ov-reset')">Annuler</button>
      <button class="fb prim" onclick="confirmReset()">🔄 Confirmer</button>
    </div>
  </div>
</div>
<div class="overlay" id="ov-restart">
  <div class="conf-card">
    <div class="conf-icon">🔄</div>
    <div class="conf-title">Redémarrer le système ?</div>
    <div class="conf-desc">Le système sera indisponible ~30 secondes. La BDD reste intacte.</div>
    <div class="conf-warn">⚠️ Toutes les sessions actives seront interrompues.</div>
    <div class="conf-btns">
      <button class="fb sec" onclick="closeOv('ov-restart')">Annuler</button>
      <button class="fb danger" onclick="confirmRestart()">🔄 Redémarrer</button>
    </div>
  </div>
</div>

<div class="toasts" id="toasts"></div>

<script>
/* ═══════════════════════════
   HELPERS
═══════════════════════════ */
const COLORS=['#E31E24','#D97706','#2563EB','#059669','#7C3AED','#DB2777','#0D9488','#EA580C'];
function ini(name){return (name||'').split(' ').map(w=>w[0]||'').join('').slice(0,2).toUpperCase()||'??'}
function color(id){return COLORS[(id-1)%COLORS.length]||COLORS[0]}
function chip(task){const m={Finaliser:'F',Vérifier:'V',Commande:'C',Réception:'R'};const c=m[task]||'A';return`<span class="chip chip-${c}">${task}</span>`}
function chipStatus(s){const m={in_progress:'prog',done:'done',pending:'wait'};const l={in_progress:'En cours',done:'Terminé',pending:'En attente'};return`<span class="chip chip-${m[s]||'e'}">${l[s]||s}</span>`}

async function api(action,data=null,method='GET'){
  const url='api.php?action='+action;
  const opts={method:method||( data?'POST':'GET'),headers:{'Content-Type':'application/json'}};
  if(data)opts.body=JSON.stringify(data);
  const r=await fetch(url,opts);
  const j=await r.json();
  if(j.error)throw new Error(j.error);
  return j;
}

function toast(type,msg){
  const icons={ok:'✅',info:'ℹ️',warn:'⚠️',err:'❌'};
  const t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<span>${icons[type]||'•'}</span><span>${msg}</span>`;
  document.getElementById('toasts').appendChild(t);
  setTimeout(()=>{t.style.animation='toastIn .22s ease reverse';setTimeout(()=>t.remove(),280)},3800);
}

/* ═══════════════════════════
   NAV
═══════════════════════════ */
const loaders={metrologues:false,projets:false,scores:false,pointages:false,horaires:false,journal:false};
function showSec(id,el){
  document.querySelectorAll('.sec').forEach(s=>s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  document.getElementById('sec-'+id).classList.add('active');
  if(el)el.classList.add('active');
  const t={dashboard:'Tableau de bord',planning:'Planning Hebdomadaire',scores:'Scores & Classement',metrologues:'Gestion des Métrologues',projets:'Gestion des Projets',pointages:'Consultation des Pointages',donnees:'Gestion des Données',parametres:'Paramètres',securite:'Sécurité & Accès',systeme:'Gestion du système'};
  document.getElementById('page-title').textContent=t[id]||id;
  if(id==='metrologues'&&!loaders.metrologues){loaders.metrologues=true;loadMetros();}
  if(id==='projets'&&!loaders.projets){loaders.projets=true;loadProjets();}
  if(id==='scores'&&!loaders.scores){loaders.scores=true;loadScores();}
  if(id==='pointages'&&!loaders.pointages){loaders.pointages=true;loadPointages();}
  if(id==='parametres'&&!loaders.horaires){loaders.horaires=true;loadHoraires();}
  if(id==='securite'&&!loaders.journal){loaders.journal=true;loadJournal();}
  if(id==='planning') loadPlanning();
}

/* ═══════════════════════════
   DASHBOARD
═══════════════════════════ */
async function loadDashboard(){
  try{
    const s=await api('dashboard_stats');
    document.getElementById('st-projets').textContent=s.projets_actifs||0;
    document.getElementById('st-metros').textContent=s.metrologues||0;
    document.getElementById('st-taches').textContent=s.taches_done||0;
  }catch(e){console.error('Stats:',e)}

  try{
    const metros=await api('metrologues_list');
    console.log('metros:', metros);
    if(!metros || metros.length===0){
      document.getElementById('plan-mini').innerHTML='<tr><td colspan="7" style="text-align:center;padding:20px;color:#9AA3B7">Aucun métrologue</td></tr>';
      document.getElementById('dash-podium').innerHTML='<div style="text-align:center;padding:20px;color:#9AA3B7">Aucun score</div>';
      return;
    }

    buildDashPodium(metros);
    buildLeaderBadge(metros);
    const sel=document.getElementById('p-metro');
    const sel2=document.getElementById('filter-metro');
    metros.forEach(m=>{
      if(sel){const o=document.createElement('option');o.value=m.id;o.textContent=m.name;sel.appendChild(o);}
      if(sel2){const o=document.createElement('option');o.value=m.id;o.textContent=m.name;sel2.appendChild(o);}
    });
  }catch(e){
    console.error('Metros:', e);
    if(document.getElementById('plan-mini')) document.getElementById('plan-mini').innerHTML='<tr><td colspan="7" style="text-align:center;padding:20px;color:#E31E24">Erreur: '+e.message+'</td></tr>';
  }
}

function buildPlanMini(metros){
  const tasks=['Finaliser','Vérifier','Commande','Réception',null,null,'Absent'];
  const b=document.getElementById('plan-mini');
  b.innerHTML=metros.slice(0,6).map(m=>{
    const ini_=ini(m.name);const col=color(m.id);
    const days=[0,1,2,3,4].map(()=>{const t=tasks[Math.floor(Math.random()*tasks.length)];return t?chip(t):'<span class="chip chip-e">—</span>';}).join('');
    return`<tr><td><div style="display:flex;align-items:center;gap:8px"><div class="ava" style="background:${col}18;color:${col}">${ini_}</div><div><div style="font-weight:600;font-size:12px"><button class="ml-btn" onclick="openMetroModal(${m.id})">${m.name}</button></div></div></div></td>${days.split('</span>').filter(Boolean).map(d=>`<td class="dc">${d}</span></td>`).join('')}<td><span style="font-weight:800;font-size:13px;color:var(--gold);font-family:'DM Mono',monospace">${m.score||0}</span></td></tr>`;
  }).join('');
  // Also build full planning table
  const bf=document.getElementById('plan-full');
  if(bf) bf.innerHTML=metros.map(m=>{
    const ini_=ini(m.name);const col=color(m.id);
    const days=[0,1,2,3,4].map(()=>{const t=tasks[Math.floor(Math.random()*tasks.length)];return`<td class="dc">${t?chip(t):'<span class="chip chip-e">—</span>'}</td>`;}).join('');
    return`<tr><td><div style="display:flex;align-items:center;gap:8px"><div class="ava" style="background:${col}18;color:${col}">${ini_}</div><div style="font-weight:600;font-size:12px"><button class="ml-btn" onclick="openMetroModal(${m.id})">${m.name}</button></div></div></td>${days}<td><span style="font-weight:800;font-size:13px;color:var(--gold);font-family:'DM Mono',monospace">${m.score||0}</span></td><td><button class="fb sec" style="padding:3px 8px;font-size:11px" onclick="openMetroModal(${m.id})">👁 Voir</button></td></tr>`;
  }).join('');
}

function buildDashPodium(metros){
  const s=[...metros].sort((a,b)=>b.score-a.score);
  const published=<?= isset($_SESSION['champion']['published']) && $_SESSION['champion']['published'] ? 'true' : 'false' ?>;
  const podTimer=document.getElementById('podium-timer');
  const podContent=document.getElementById('podium-content');
  if(published && s.length>=1){
    if(podTimer) podTimer.style.display='none';
    if(podContent) podContent.style.display='block';
    if(podContent){
      podContent.innerHTML=s.slice(0,3).map((m,i)=>`
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--bdr)">
         <div style="font-size:18px">${['🥇','🥈','🥉'][i]}</div>
         <div style="flex:1;font-weight:600;font-size:13px">${m.name}</div>
         <div style="font-weight:800;font-size:15px;color:var(--gold);font-family:'DM Mono',monospace">${m.score||0} pts</div>
        </div>`).join('');
}
  } else {
    if(podTimer) podTimer.style.display='block';
    if(podContent) podContent.style.display='none';
  }
  if(s[0]){
    document.getElementById('champ-name').textContent=s[0].name;
    document.getElementById('champ-score').textContent=s[0].score||0;
    champData=s[0];
  }
}

function buildLeaderBadge(metros){
  const s=[...metros].sort((a,b)=>b.score-a.score);
  const lb=document.getElementById('leader-badge');
  if(lb && s[0]) lb.innerHTML=`🏅 <strong style="color:var(--gold)">Leader :</strong> ${s[0].name} — <strong style="color:var(--gold)">${s[0].score||0} pts</strong>`;
}

/* ═══════════════════════════
   MÉTROLOGUES
═══════════════════════════ */
async function loadMetros(){
  try{
    const metros=await api('metrologues_list');
    const el=document.getElementById('metro-grid');
    el.innerHTML=metros.map(m=>{
      const i=ini(m.name);const col=color(m.id);
      return`<div class="metro-card" onclick="openMetroModal(${m.id})">
        <div class="mc-top">
          <div class="ava ava-lg" style="background:${col}18;color:${col}">${i}</div>
          <div class="mc-info"><strong>${m.name}</strong><span>${m.niveau||'—'} · Classeur #${m.classeur_number||'—'}</span></div>
          <div class="mc-badge">${(m.score||0)>800?'🏆':(m.score||0)>600?'⭐':'💼'}</div>
        </div>
        <div class="mc-stats">
          <div class="mc-stat"><div class="mc-stat-val" style="color:var(--gold)">${m.score||0}</div><div class="mc-stat-key">Score</div></div>
          <div class="mc-stat"><div class="mc-stat-val" style="color:var(--blue)">${m.projets||0}</div><div class="mc-stat-key">Projets</div></div>
          <div class="mc-stat"><div class="mc-stat-val" style="color:${(m.absences||0)>4?'var(--red)':'var(--green)'}">${m.absences||0}</div><div class="mc-stat-key">Absences</div></div>
        </div>
        <div style="margin-top:10px;padding-top:8px;border-top:1px solid var(--bdr);font-size:11px;color:var(--txt3)">
          📺 TFT/LED Classeur <strong style="color:var(--red)">#${m.classeur_number||'—'}</strong> · ${m.specialite||'—'}
        </div>
      </div>`;
    }).join('');
  }catch(e){toast('err','Erreur chargement métrologues : '+e.message)}
}

let currentMetroId=null,champData=null,champPublished=false;

async function openMetroModal(id){
  currentMetroId=id;
  openOv('ov-metro');
  document.getElementById('mm-name').textContent='Chargement...';
  try{
    const m=await api('metro_detail&id='+id);
    const col=color(m.id);const i=ini(m.name);
    document.getElementById('mm-av').textContent=i;
    document.getElementById('mm-av').style.background=`linear-gradient(135deg,${col},${col}99)`;
    document.getElementById('mm-name').textContent=m.name;
    document.getElementById('mm-role').textContent=`${m.niveau||'—'} · ${m.specialite||'—'} · Classeur #${m.classeur_number||'—'}`;
    document.getElementById('mm-score').textContent=m.score||0;
    document.getElementById('mm-projs').textContent=(m.projects||[]).length;
    document.getElementById('mm-pres').textContent='ID #'+m.id;
    document.getElementById('mm-abs').textContent=(m.absences_list||[]).length;
    // Infos
    document.getElementById('mm-infos').innerHTML=`
      <div><span class="fl">ID</span><div style="font-family:'DM Mono',monospace;font-weight:700">MTR-${String(m.id).padStart(3,'0')}</div></div>
      <div><span class="fl">Classeur TFT/LED</span><div style="font-family:'DM Mono',monospace;font-weight:800;color:var(--red)">#${m.classeur_number||'—'}</div></div>
      <div><span class="fl">Poste</span><div style="font-weight:600">${m.poste||'—'}</div></div>`;
    // Projets
    document.getElementById('mm-projets').innerHTML=(m.projects||[]).length
      ? (m.projects||[]).map(p=>`<div class="pli"><div class="pli-dot" style="background:${col}"></div><div class="pli-name">${p.title}</div>${chipStatus(p.status)}</div>`).join('')
      : `<div style="color:var(--txt3);font-size:12px;padding:6px">Aucun projet</div>`;
    // Absences
    document.getElementById('mm-absences').innerHTML=(m.absences_list||[]).length
      ? (m.absences_list||[]).map(d=>`<span class="abs-chip">📅 ${d}</span>`).join('')
      : `<span style="color:var(--txt3);font-size:12px">Aucune absence ce trimestre</span>`;
  }catch(e){document.getElementById('mm-name').textContent='Erreur : '+e.message}
}

function openAddMetro(){
  document.getElementById('metro-modal-title').textContent='🔧 Nouveau Métrologue';
  document.getElementById('m-id').value='';
  ['m-name','m-email','m-tel','m-poste','m-pass'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  document.getElementById('m-classeur').value='';
  document.getElementById('pass-field').style.display='block';
  openOv('ov-addmetro');
}

function openEditMetro(){
  closeOv('ov-metro');
  document.getElementById('metro-modal-title').textContent='✏️ Modifier Métrologue';
  document.getElementById('pass-field').style.display='none';
  // Pre-fill depuis le modal précédent (on recharge)
  api('metro_detail&id='+currentMetroId).then(m=>{
    document.getElementById('m-id').value=m.id;
    document.getElementById('m-name').value=m.name;
    document.getElementById('m-email').value=m.email;
    document.getElementById('m-tel').value=m.telephone||'';
    document.getElementById('m-poste').value=m.poste||'';
    document.getElementById('m-classeur').value=m.classeur_number||'';
    document.getElementById('m-niveau').value=m.niveau||'Junior';
    document.getElementById('m-spec').value=m.specialite||'Calibration';
  });
  openOv('ov-addmetro');
}

async function saveMetro(){
  const id=document.getElementById('m-id').value;
  const data={
    id:id?parseInt(id):null,
    name:document.getElementById('m-name').value.trim(),
    email:document.getElementById('m-email').value.trim(),
    telephone:document.getElementById('m-tel').value.trim(),
    poste:document.getElementById('m-poste').value.trim(),
    niveau:document.getElementById('m-niveau').value,
    specialite:document.getElementById('m-spec').value,
    classeur_number:parseInt(document.getElementById('m-classeur').value)||0,
    password:document.getElementById('m-pass')?.value||''
  };
  if(!data.name||!data.email){toast('err','❌ Nom et email obligatoires');return}
  try{
    await api(id?'metro_edit':'metro_add', data, 'POST');
    closeOv('ov-addmetro');
    toast('ok',`✅ Métrologue ${data.name} enregistré !`);
    loaders.metrologues=false;loadMetros();
  }catch(e){toast('err','❌ '+e.message)}
}

async function deleteMetro(){
  if(!confirm('Supprimer ce métrologue ?')) return;
  try{
    await api('metro_delete',{id:currentMetroId},'POST');
    closeOv('ov-metro');
    toast('warn','🗑️ Métrologue supprimé');
    loaders.metrologues=false;loadMetros();
  }catch(e){toast('err','❌ '+e.message)}
}

/* ═══════════════════════════
   PROJETS
═══════════════════════════ */
async function loadProjets(){
  try{
    const projs=await api('projets_list');
    const el=document.getElementById('proj-grid');
    const COLS=['#E31E24','#D97706','#2563EB','#059669','#7C3AED','#DB2777','#0D9488','#EA580C'];
    el.innerHTML=projs.map((p,i)=>{
      const col=COLS[i%8];
      const steps=(p.steps||[]).map(s=>`<div class="pt chip-${s.name==='Finaliser'?'F':s.name==='Vérifier'?'V':s.name==='Commande'?'C':'R'} ${s.status==='done'?'done':''}"><span>${s.status==='done'?'✅':'⬜'}</span>${s.name}</div>`).join('');
      return`<div class="proj-card">
        <div class="pj-top"><span style="font-size:10.5px;font-weight:700;color:${col};background:${col}12;padding:2px 7px;border-radius:20px;border:1px solid ${col}22">Métrologie</span>${chipStatus(p.status)}</div>
        <div class="pj-name">${p.title}</div>
        <div class="pj-tasks">${steps}</div>
        <div class="pb-lbl"><span>Avancement</span><span>${p.progress||0}%</span></div>
        <div class="pb-bg"><div class="pb-fill" style="width:${p.progress||0}%;background:${col}"></div></div>
        <div class="pj-assign">
          <div class="ava" style="background:#E31E2418;color:var(--red)">${ini(p.metro_name||'')}</div>
          <span>${p.metro_name||'—'}</span>
          <span style="margin-left:auto;font-size:10.5px;color:var(--txt3)">Échéance : ${p.deadline||'—'}</span>
        </div>
      </div>`;
    }).join('');
  }catch(e){toast('err','Erreur projets : '+e.message)}
}

async function openAddProj(){
  const sel=document.getElementById('p-metro');
  if(sel.options.length<=1){
    try{
      const metros=await api('metrologues_list');
      metros.forEach(m=>{
        const o=document.createElement('option');
        o.value=m.id;
        o.textContent=m.name;
        sel.appendChild(o);
      });
    }catch(e){}
  }
  openOv('ov-addproj');
}
async function saveProj(){
  const data={
    title:document.getElementById('p-nom').value.trim(),
    assigned_to:parseInt(document.getElementById('p-metro').value)||0,
    deadline:document.getElementById('p-deadline').value||null,
    priority:document.getElementById('p-prio').value,
    description:document.getElementById('p-desc').value.trim()
  };
  if(!data.title||!data.assigned_to){toast('err','❌ Titre et métrologue obligatoires');return}
  try{
    await api('projet_add',data,'POST');
    closeOv('ov-addproj');
    toast('ok','✅ Projet créé avec les 4 tâches !');
    loaders.projets=false;loadProjets();
  }catch(e){toast('err','❌ '+e.message)}
}

/* ═══════════════════════════
   SCORES
═══════════════════════════ */
async function loadScores(){
  try{
    const rows=await api('scores_list');
    const mx=(rows[0]?.score)||1;
    const COLS=['#E31E24','#D97706','#2563EB','#059669','#7C3AED','#DB2777','#0D9488','#EA580C'];
    // Podium
    const pod=document.getElementById('scores-podium');
    if(rows.length>=3){
      pod.innerHTML=`<div class="podium">
        <div class="pp pp2"><div class="pa-w"><div class="pp-av" style="width:40px;height:40px">${ini(rows[1]?.name)}</div><div class="pp-crown">🥈</div></div><div class="pp-name">${(rows[1]?.name||'').split(' ')[0]}</div><div class="pp-pts">${rows[1]?.score||0}</div><div class="pp-bar">2ème</div></div>
        <div class="pp pp1"><div class="pa-w"><div class="pp-av" style="width:46px;height:46px">${ini(rows[0]?.name)}</div><div class="pp-crown">👑</div></div><div class="pp-name">${(rows[0]?.name||'').split(' ')[0]}</div><div class="pp-pts">${rows[0]?.score||0}</div><div class="pp-bar">1er</div></div>
        <div class="pp pp3"><div class="pa-w"><div class="pp-av" style="width:36px;height:36px">${ini(rows[2]?.name)}</div><div class="pp-crown">🥉</div></div><div class="pp-name">${(rows[2]?.name||'').split(' ')[0]}</div><div class="pp-pts">${rows[2]?.score||0}</div><div class="pp-bar">3ème</div></div>
      </div>`;
    }
    // Liste
    const list=document.getElementById('scores-list');
    list.innerHTML=rows.map((r,i)=>{const col=COLS[i%8];return`<div class="score-row"><div class="s-rank">${i+1}</div><div class="ava" style="background:${col}18;color:${col}">${ini(r.name)}</div><div class="s-name">${r.name}</div><div class="s-bar-w"><div class="s-bar-bg"><div class="s-bar" style="width:${Math.round((r.score/mx)*100)}%;background:${col}"></div></div></div><div class="s-pts">${r.score||0}</div></div>`;}).join('');
  }catch(e){toast('err','Erreur scores : '+e.message)}
}

/* ═══════════════════════════
   POINTAGES
═══════════════════════════ */
async function loadPointages(){
  try{
    const userId=document.getElementById('filter-metro').value;
    const date=document.getElementById('filter-date').value;
    let url='pointages_list';
    if(userId)url+='&user_id='+userId;
    if(date)url+='&date='+date;
    const rows=await api(url);
    const b=document.getElementById('pointage-body');
    b.innerHTML=rows.map(r=>{
      const sc={ok:'ok',late:'late',absent:'abs'}[r.statut]||'abs';
      const sl={ok:'À l\'heure',late:'Retard',absent:'Absent'}[r.statut]||r.statut;
      const ini_=ini(r.metro_name||'');
      return`<tr>
        <td><div style="display:flex;align-items:center;gap:7px"><div class="ava" style="background:var(--red-bg);color:var(--red)">${ini_}</div><div><div style="font-weight:600;font-size:12px">${r.metro_name}</div><div style="font-size:10.5px;color:var(--txt3)">Classeur #${r.classeur_number||'—'}</div></div></div></td>
        <td style="font-family:'DM Mono',monospace;font-size:12px">${r.date}</td>
        <td style="font-family:'DM Mono',monospace;font-weight:600">${r.check_in||'—'}</td>
        <td style="font-family:'DM Mono',monospace;font-weight:600">${r.check_out||'—'}</td>
        <td style="font-family:'DM Mono',monospace">${r.duree||'—'}</td>
        <td><span class="chip pres-${sc}" style="padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600">${sl}</span></td>
        <td style="font-size:11.5px;color:var(--txt3)">${r.note||'—'}</td>
      </tr>`;
    }).join('')||'<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--txt3)">Aucun enregistrement</td></tr>';
  }catch(e){toast('err','Erreur pointages : '+e.message)}
}

/* ═══════════════════════════
   HORAIRES
═══════════════════════════ */
async function loadHoraires(){
  try{
    const rows=await api('horaires_get');
    const el=document.getElementById('horaires-list');
    el.innerHTML=rows.map(r=>`<div class="hor-row">
      <div class="ava" style="background:var(--red-bg);color:var(--red)">${ini(r.name)}</div>
      <div class="hor-name">${r.name}</div>
      <div class="hor-time">
        <span style="font-size:10.5px;color:var(--txt3)">Arrivée</span>
        <input class="hor-inp" type="time" value="${r.work_start||'08:00'}" data-id="${r.id}" data-type="work_start"/>
        <span style="font-size:10.5px;color:var(--txt3)">→</span>
        <input class="hor-inp" type="time" value="${r.work_end||'17:00'}" data-id="${r.id}" data-type="work_end"/>
        <span style="font-size:10.5px;color:var(--txt3)">Départ</span>
      </div>
    </div>`).join('');
  }catch(e){toast('err','Erreur horaires : '+e.message)}
}

async function saveHoraires(){
  const horaires=[];
  document.querySelectorAll('.hor-inp').forEach(inp=>{
    let h=horaires.find(x=>x.user_id===inp.dataset.id);
    if(!h){h={user_id:inp.dataset.id};horaires.push(h);}
    h[inp.dataset.type]=inp.value;
  });
  try{
    await api('horaires_save',{horaires},'POST');
    toast('ok','✅ Horaires enregistrés !');
  }catch(e){toast('err','❌ '+e.message)}
}

/* ═══════════════════════════
   JOURNAL
═══════════════════════════ */
async function loadJournal(){
  try{
    const rows=await api('journal_get');
    const el=document.getElementById('journal-body');
    if(!rows.length){el.innerHTML='<div style="color:var(--txt3);font-size:12px;text-align:center;padding:20px">Aucune action enregistrée</div>';return;}
    el.innerHTML=rows.map(l=>`<div class="log-row"><div class="log-ico">${l.ico||'•'}</div><div class="log-msg">${l.action} ${l.details?'— '+l.details:''}</div><span class="log-type log-${l.type||'ok'}">${l.type==='ok'?'OK':l.type==='warn'?'AVERT.':'ERR.'}</span><div class="log-time">${l.time}</div></div>`).join('');
  }catch(e){toast('err','Erreur journal : '+e.message)}
}

/* ═══════════════════════════
   SÉCURITÉ
═══════════════════════════ */
async function saveProfile(){
  try{
    await api('update_profile',{name:document.getElementById('sec-name').value,email:document.getElementById('sec-email').value},'POST');
    toast('ok','✅ Profil mis à jour');
  }catch(e){toast('err','❌ '+e.message)}
}
async function changePassword(){
  const o=document.getElementById('sec-old').value;
  const n=document.getElementById('sec-new').value;
  const c=document.getElementById('sec-conf').value;
  if(n!==c){toast('err','❌ Mots de passe différents');return}
  if(n.length<6){toast('err','❌ Trop court (min 6 caractères)');return}
  try{
    await api('change_password',{old_password:o,new_password:n},'POST');
    ['sec-old','sec-new','sec-conf'].forEach(id=>document.getElementById(id).value='');
    toast('ok','✅ Mot de passe modifié !');
  }catch(e){toast('err','❌ '+e.message)}
}

/* ═══════════════════════════
   RESET / RESTART / DELETE
═══════════════════════════ */
async function confirmReset(){
  try{
    await api('scores_reset',{},'POST');
    closeOv('ov-reset');
    toast('warn','🔄 Scores remis à zéro !');
    loaders.scores=false;
  }catch(e){toast('err','❌ '+e.message)}
}
async function confirmRestart(){
  try{
    await api('system_restart',{},'POST');
    closeOv('ov-restart');
    toast('info','🔄 Signal de redémarrage envoyé...');
    setTimeout(()=>toast('ok','✅ Système redémarré !'),2500);
  }catch(e){toast('err','❌ '+e.message)}
}
async function confirmDeleteOld(){
  const d=document.getElementById('delete-before').value;
  if(!d){toast('err','❌ Sélectionnez une date');return}
  if(!confirm(`Supprimer les pointages avant le ${d} ?`)) return;
  try{
    await api('delete_old',{before_date:d},'POST');
    toast('ok',`✅ Pointages avant ${d} supprimés`);
  }catch(e){toast('err','❌ '+e.message)}
}

/* ═══════════════════════════
   CHAMPION
═══════════════════════════ */
function openChamp(){startConf();document.getElementById('ov-champ').classList.add('open');document.getElementById('champ-note').innerHTML=champPublished?'<span style="color:var(--green)">✅ Déjà publié</span>':'';}
function closeChamp(){document.getElementById('ov-champ').classList.remove('open');stopConf()}
document.getElementById('ov-champ').addEventListener('click',e=>{if(e.target===document.getElementById('ov-champ'))closeChamp()});
async function publishChamp(){
  try{
    if(champData) await api('champion_publish',{user_id:champData.id},'POST');
    champPublished=true;closeChamp();
    toast('ok','📢 Résultat publié sur la plateforme métrologue !');
  }catch(e){toast('err','❌ '+e.message)}
}
const confCols=['#E31E24','#D97706','#E31E24','#2563EB','#059669'];
function startConf(){const c=document.getElementById('conf-c');c.innerHTML='';for(let i=0;i<55;i++){const p=document.createElement('div');p.className='conf-p';p.style.cssText=`left:${Math.random()*100}%;background:${confCols[Math.floor(Math.random()*confCols.length)]};border-radius:${Math.random()>.5?'50%':'2px'};width:${5+Math.random()*7}px;height:${7+Math.random()*9}px;animation-duration:${2+Math.random()*3}s;animation-delay:${Math.random()*2}s`;c.appendChild(p);}}
function stopConf(){document.getElementById('conf-c').innerHTML=''}

/* ═══════════════════════════
   MODALS
═══════════════════════════ */
function openOv(id){document.getElementById(id).classList.add('open')}
function closeOv(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open')}));

/* ═══════════════════════════
   TIMER
═══════════════════════════ */
function tick(){
  // Fin de trimestre = dernier jour du trimestre courant
  const now=new Date();const q=Math.ceil((now.getMonth()+1)/3);
  const endMonth=[2,5,8,11][q-1];
  const lastDay=new Date(now.getFullYear(),endMonth+1,0,23,59,59);
  const diff=Math.max(0,lastDay-now);
  const d=Math.floor(diff/86400000),h=Math.floor((diff%86400000)/3600000),mn=Math.floor((diff%3600000)/60000);
  const f=n=>String(n).padStart(2,'0');
  ['td1','td2'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(d)});
  ['th1','th2'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(h)});
  ['tm1','tm2'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(mn)});
  ['td3'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(d)});
  ['th3'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(h)});
  ['tm3'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(mn)});
  // Progress bar
  const startMonth=[0,3,6,9][q-1];
  const start=new Date(now.getFullYear(),startMonth,1);
  const end=lastDay;
  const pct=Math.round(((now-start)/(end-start))*100);
  const pb=document.getElementById('trim-bar');if(pb)pb.style.width=pct+'%';
  const pp=document.getElementById('trim-pct');if(pp)pp.textContent=pct+'%';
}
setInterval(tick,1000);tick();

/* ═══════════════════════════
   INIT
═══════════════════════════ */
async function loadPlanning(){
  console.log('loadPlanning appelée');
  try{
    console.log('avant api call');
    const metros=await api('metrologues_list');
    console.log('metros reçus:', metros.length);
    const tasks=['Finaliser','Vérifier','Commande','Réception',null,null,'Absent'];
    const b=document.getElementById('plan-full');
    console.log('plan-full element:', b);
    if(!b) return;
    if(metros.length===0){
  b.innerHTML='<tr><td colspan="8" style="text-align:center;padding:20px;color:#9AA3B7">Aucun métrologue</td></tr>';
  return;
}
const tasksList=['Finaliser','Vérifier','Commande','Réception',null,null,'Absent'];
const TMAP={Finaliser:'F',Vérifier:'V',Commande:'C',Réception:'R',Absent:'A'};
b.innerHTML=metros.map(m=>{
  const col=color(m.id);
  const days=[0,1,2,3,4].map(()=>{
    const t=tasksList[Math.floor(Math.random()*tasksList.length)];
    return`<td style="text-align:center">${t?`<span class="chip chip-${TMAP[t]||'e'}">${t}</span>`:'<span class="chip chip-e">—</span>'}</td>`;
  }).join('');
  return`<tr>
    <td><div style="display:flex;align-items:center;gap:8px">
      <div class="ava" style="background:${col}18;color:${col}">${ini(m.name)}</div>
      <div style="font-weight:600;font-size:12px">${m.name}</div>
    </div></td>
    ${days}
    <td><span style="font-weight:800;font-size:13px;color:var(--gold)">${m.score||0}</span></td>
    <td><button class="fb sec" style="padding:3px 8px;font-size:11px" onclick="openMetroModal(${m.id})">👁 Voir</button></td>
  </tr>`;
}).join('');
console.log('HTML injecté dans plan-full');
  }catch(e){console.error('Planning:',e)}
}

loadDashboard();
</script>
</body>
</html>