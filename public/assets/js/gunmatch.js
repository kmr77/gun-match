/**
 * GunMatch 共通UIの最小限の動作。
 * 検索・ログイン・お気に入り等の業務ロジックはここで仮実装しない。
 */
(() => {
  'use strict';

  const toggle = document.querySelector('[data-menu-toggle]');
  const navigation = document.querySelector('[data-navigation]');
  if (!toggle || !navigation) return;

  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'メニューを閉じる' : 'メニューを開く');
    navigation.classList.toggle('is-open', open);
    document.body.classList.toggle('menu-is-open', open);
  };

  toggle.addEventListener('click', () => {
    setOpen(toggle.getAttribute('aria-expanded') !== 'true');
  });

  navigation.addEventListener('click', (event) => {
    if (event.target.closest('a')) setOpen(false);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
      setOpen(false);
      toggle.focus();
    }
  });

  document.addEventListener('click', (event) => {
    if (!navigation.contains(event.target) && !toggle.contains(event.target)) setOpen(false);
  });

  window.matchMedia('(min-width: 961px)').addEventListener('change', (event) => {
    if (event.matches) setOpen(false);
  });
})();
