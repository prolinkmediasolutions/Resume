'use strict';
const menu = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#navigation');
function closeMenu() { navigation.classList.remove('is-open'); menu.setAttribute('aria-expanded', 'false'); }
menu.addEventListener('click', () => { const open = menu.getAttribute('aria-expanded') !== 'true'; menu.setAttribute('aria-expanded', String(open)); navigation.classList.toggle('is-open', open); });
navigation.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
document.querySelector('#year').textContent = new Date().getFullYear();
document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => {
  document.querySelectorAll('[data-filter]').forEach(item => { item.classList.toggle('active', item === button); item.setAttribute('aria-pressed', String(item === button)); });
  document.querySelectorAll('.project').forEach(project => { project.hidden = button.dataset.filter !== 'all' && project.dataset.category !== button.dataset.filter; });
}));
let projects = [{"title": "Omega Fire Ministries, Los Angeles", "category": "development", "type": "Church media & website development", "description": "Media support and website development for Omega Fire Ministries, Los Angeles.", "challenge": "Support the ministry\u2019s online presence and media communication.", "approach": "Contributed website development and media support aligned with the ministry\u2019s communication needs."}, {"title": "LineageCare Foundation", "category": "development", "type": "Foundation website", "description": "Website work for LineageCare Foundation.", "challenge": "Present the foundation through a clear digital presence.", "approach": "Worked on the foundation\u2019s website to communicate its identity and work."}, {"title": "Church of God Mission Sunderland", "category": "development", "type": "Church website", "description": "Website work for Church of God Mission Sunderland.", "challenge": "Support the church\u2019s digital presence.", "approach": "Contributed website work focused on communicating the church\u2019s identity."}, {"title": "CGMI Diaspora", "category": "development", "type": "Ministry website", "description": "Website work for CGMI Diaspora.", "challenge": "Support the ministry\u2019s online communication.", "approach": "Contributed website development for its digital presence."}, {"title": "Matthew Omondiale Mentorship Institute", "category": "development", "type": "Institute website", "description": "Website development for Matthew Omondiale Mentorship Institute.", "challenge": "Present the institute and its mentorship focus online.", "approach": "Worked on a website suited to the institute\u2019s identity and communication needs."}, {"title": "SMTI", "category": "development", "type": "Institutional website", "description": "Website work for SMTI.", "challenge": "Support the institution with an online presence.", "approach": "Contributed website development aligned with the institution\u2019s requirements."}, {"title": "Livingway World Church", "category": "strategy", "type": "Complete media setup", "description": "Total media setup for Livingway World Church.", "challenge": "Bring together the media components needed for church communication.", "approach": "Delivered a complete media setup for the church."}];
const dialog = document.querySelector('#project-dialog');
document.querySelectorAll('[data-project]').forEach(button => button.addEventListener('click', () => {
  const project = projects[Number(button.dataset.project)];
  document.querySelector('#dialog-title').textContent = project.title;
  document.querySelector('#dialog-description').textContent = project.description;
  document.querySelector('#dialog-challenge').textContent = project.challenge;
  document.querySelector('#dialog-approach').textContent = project.approach;
  dialog.showModal(); document.body.classList.add('modal-open');
}));
document.querySelector('.dialog-close').addEventListener('click', () => dialog.close());
dialog.addEventListener('close', () => document.body.classList.remove('modal-open'));
dialog.addEventListener('click', event => { const rect = dialog.getBoundingClientRect(); if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close(); });

fetch('api.php?route=content').then(response => response.ok ? response.json() : null).then(content => {
 if (!content) return;
 document.querySelectorAll('[data-content]').forEach(el => { const value = content[el.dataset.content]; if (typeof value === 'string') el.textContent = value; });
 document.title = content.name + ' — Resume & Portfolio';
 const phoneLink = document.querySelector('[data-phone-link]');
 if (phoneLink) { phoneLink.href = 'tel:' + content.phone.replace(/[^+0-9]/g, ''); phoneLink.firstChild.textContent = content.phone + ' '; }
 if (Array.isArray(content.projects)) { projects = content.projects; document.querySelectorAll('.project').forEach((card,i) => {const p=projects[i]; if(!p)return; card.querySelector('h3').textContent=p.title;card.querySelector('.client-title').textContent=p.title;card.querySelector('.project-info p').textContent=p.type;card.querySelector('button').setAttribute('aria-label','Read about '+p.title);}); }
}).catch(() => {});
