'use strict';
document.querySelector('#print-resume').addEventListener('click',()=>window.print());
fetch('api.php?route=content').then(r=>r.ok?r.json():null).then(c=>{
 if(!c)return;
 document.querySelector('h1').textContent=c.name;document.title=c.name+' — Resume';
 document.querySelector('header>p').textContent=c.title;
 const contact=document.querySelector('.contact');contact.replaceChildren();contact.append(document.createTextNode(c.address),document.createElement('br'));
 const phone=document.createElement('a');phone.textContent=c.phone;phone.href='tel:'+c.phone.replace(/[^+0-9]/g,'');contact.append(phone);
 document.querySelector('main>section>p').textContent=c.summary;
 const section=document.querySelector('#resume-projects');section.replaceChildren();const heading=document.createElement('h2');heading.textContent='Selected experience';section.append(heading);
 c.projects.forEach(p=>{const a=document.createElement('article'),h=document.createElement('h3'),meta=document.createElement('p'),description=document.createElement('p');h.textContent=p.title;meta.className='meta';meta.textContent=p.type;description.textContent=p.description;a.append(h,meta,description);section.append(a);});
}).catch(()=>{});
