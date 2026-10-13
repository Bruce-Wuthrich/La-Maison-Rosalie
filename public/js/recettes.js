// Les filtres gardent leur fonctionnement classique.
document.querySelectorAll('[data-auto-submit]').forEach((select) => {
  select.addEventListener('change', () => select.form.requestSubmit());
});

// Les commentaires et la notation partagent un verrou pour éviter les envois concurrents.
(() => {
  const section = document.querySelector('[data-comments-section]');
  if (!section) return;

  const form = section.querySelector('.comment-form');
  const guestPanel = section.querySelector('.comment-login-panel');
  const feed = section.querySelector('.comments-list-panel');
  const empty = section.querySelector('.comments-empty');
  const pagination = section.querySelector('.comment-pagination');
  const more = section.querySelector('.comment-more');
  const status = section.querySelector('[data-comment-status]');
  const radios = [...form.querySelectorAll('input[name="rating"]')];
  const ratingFieldset = form.querySelector('.comment-rating-fieldset');
  const message = form.elements.message;
  const subject = form.elements.subject;
  let busy = false;
  let pendingAuthRefresh = false;
  let page = Number(section.dataset.commentPage) || 1;
  let total = Number(section.dataset.commentCount) || 0;
  let sort = section.dataset.commentSort || 'date';
  let confirmedRating = Number(radios.find((input) => input.checked)?.value) || 0;
  let memberId = Number(section.dataset.userId) || 0;
  let canRate = memberId > 0 && memberId !== Number(section.dataset.authorId);

  const notify = (type, text) => {
    status.textContent = text;
    status.setAttribute('role', type === 'error' ? 'alert' : 'status');
    if (type !== 'loading') showFlash(type, text);
  };

  const restorePersonalRating = () => {
    radios.forEach((input) => { input.checked = Number(input.value) === confirmedRating; });
  };

  const setBusy = (value) => {
    busy = value;
    section.setAttribute('aria-busy', String(value));
    // inert bloque les interactions sans exclure les radios du FormData.
    ratingFieldset.inert = value;
    radios.forEach((input) => { input.disabled = !canRate; });
    section.querySelectorAll('button[type="submit"]').forEach((button) => {
      button.disabled = value;
    });
    more.setAttribute('aria-disabled', String(value));
  };

  const updateRating = (stats) => {
    const average = stats.average === null ? null : Number(stats.average);
    const filled = average === null ? 0 : Math.round(average);
    document.querySelectorAll('.recipe-rating-stars, .comments-average-stars').forEach((group) => {
      group.querySelectorAll('img').forEach((image, index) => {
        image.src = `assets/img/recettes/icons/star-${index < filled ? 'filled' : 'outline'}.svg?v=${document.body.dataset.iconVersion}`;
      });
    });
    const formatted = average === null ? null : average.toFixed(1).replace('.', ',');
    const summary = document.querySelector('.recipe-rating-stats');
    if (summary) summary.textContent = formatted === null
      ? 'Aucune note pour le moment' : `${formatted}/5 — ${stats.count} avis`;
    document.querySelector('.recipe-rating')?.setAttribute('aria-label', formatted === null
      ? 'Cette recette n’a pas encore de note' : `Note moyenne : ${formatted} sur 5`);
  };

  const updateCounts = () => {
    document.querySelectorAll('[data-comment-count]:not([data-comments-section])').forEach((node) => {
      node.textContent = `${total} commentaire${total === 1 ? '' : 's'}`;
    });
    empty.hidden = feed.children.length > 0;
    pagination.hidden = page * 10 >= total;
    more.href = `?pg=recette&slug=${encodeURIComponent(section.dataset.slug)}&comment_sort=${sort}&comment_page=${page + 1}#comments`;
    [...feed.children].forEach((article, index) => {
      article.classList.toggle('recipe-comment-reversed', index % 2 === 1);
    });
  };

  const element = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };

  const hiddenInput = (name, value) => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    return input;
  };

  const renderComment = (comment) => {
    const article = element('article', 'recipe-comment');
    article.dataset.commentId = String(comment.id);
    const avatar = element('div', 'comment-avatar');
    avatar.setAttribute('aria-hidden', 'true');
    avatar.append(element('span'));
    const body = element('div', 'comment-body');
    const authorLine = element('div', 'comment-author-line');
    authorLine.append(element('h3', '', comment.author));
    const authorRating = Number(comment.authorRating) || 0;
    if (authorRating > 0) {
      const stars = element('div', 'comment-stars');
      stars.setAttribute('role', 'img');
      stars.setAttribute('aria-label', `Note : ${authorRating} sur 5`);
      for (let star = 1; star <= 5; star += 1) {
        const image = document.createElement('img');
        image.src = `assets/img/recettes/icons/star-${star <= authorRating ? 'filled' : 'outline'}.svg?v=${document.body.dataset.iconVersion}`;
        image.alt = '';
        stars.append(image);
      }
      authorLine.append(stars);
    }
    const date = element('time');
    date.dateTime = comment.createdAt;
    // MariaDB renvoie YYYY-MM-DD HH:MM:SS : même affichage que PHP, sans conversion de fuseau.
    const parts = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(comment.createdAt || '');
    date.textContent = parts ? `${parts[3]}/${parts[2]}/${parts[1]} ${parts[4]}:${parts[5]}` : '';
    authorLine.append(date);
    body.append(authorLine);
    if (comment.subject) body.append(element('h4', 'comment-subject', comment.subject));
    body.append(element('p', 'comment-message', comment.message));
    const footer = element('div', 'comment-footer-row');
    if (comment.canDelete === true && memberId > 0) {
      const deletion = document.createElement('form');
      deletion.action = '?pg=commentaire&action=delete';
      deletion.method = 'post';
      deletion.append(
        hiddenInput('csrf_token', form.elements.csrf_token.value),
        hiddenInput('comment_id', comment.id),
        hiddenInput('slug', section.dataset.slug),
      );
      const button = element('button', 'comment-delete', 'Supprimer');
      button.type = 'submit';
      deletion.append(button);
      footer.append(deletion);
    }
    body.append(footer);
    article.append(avatar, body);
    return article;
  };

  const readPage = async (requestedPage) => {
    const url = new URL(window.location.href);
    url.search = new URLSearchParams({
      pg: 'commentaires', slug: section.dataset.slug, sort, page: String(requestedPage),
    });
    url.hash = '';
    const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
    const result = await response.json();
    if (!response.ok || result.success !== true) throw new Error(result.message || 'Lecture impossible.');
    return result.data;
  };

  const updateMember = (data) => {
    memberId = Number(data.user?.id) || 0;
    canRate = data.canRate === true;
    form.hidden = memberId === 0;
    guestPanel.hidden = memberId !== 0;
    form.querySelector('[data-comment-username]').value = data.user?.username || '';
    form.querySelector('[data-comment-email]').value = data.user?.email || '';
    form.elements.csrf_token.value = data.csrfToken;
    confirmedRating = Number(data.userRating) || 0;
    restorePersonalRating();
    updateRating(data.rating);
  };

  const refresh = async () => {
    const data = await readPage(1);
    updateMember(data);
    feed.replaceChildren(...data.comments.map(renderComment));
    page = data.page;
    total = data.total;
    updateCounts();
  };

  const runAction = async (task, loadingText) => {
    if (busy) return;
    setBusy(true);
    notify('loading', loadingText);
    try {
      await task();
    } catch (error) {
      restorePersonalRating();
      notify('error', error.message || 'Le serveur ne répond pas. Réessayez.');
    } finally {
      setBusy(false);
      if (pendingAuthRefresh) {
        pendingAuthRefresh = false;
        void runAction(async () => { await refresh(); status.textContent = ''; }, 'Actualisation de la session…');
      }
    }
  };

  radios.forEach((input) => {
    input.addEventListener('change', () => {
      if (busy || !canRate) { restorePersonalRating(); return; }
      const data = new FormData(form);
      data.delete('message');
      data.delete('subject');
      void runAction(async () => {
        const response = await fetch('?pg=noter', {
          method: 'POST', headers: { Accept: 'application/json' }, body: data,
        });
        const result = await response.json();
        if (!response.ok || result.success !== true) throw new Error(result.message);
        confirmedRating = Number(result.data.userRating);
        restorePersonalRating();
        updateRating(result.data);
        notify('success', result.message);
      }, 'Enregistrement de votre note…');
    });
  });

  // Validation après trim, comme le backend ; la saisie est conservée en cas d'erreur.
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (busy) return;
    if (memberId === 0) { guestPanel.querySelector('[data-auth-open]').click(); return; }
    const length = [...message.value.trim()].length;
    message.setCustomValidity(length < 3 || length > 500
      ? 'Le commentaire doit faire entre 3 et 500 caractères.' : '');
    subject.setCustomValidity([...subject.value.trim()].length > 120
      ? 'Le sujet doit faire au maximum 120 caractères.' : '');
    if (!form.reportValidity()) return;
    void runAction(async () => {
      const result = await sendJsonForm(form);
      if (result.success !== true) throw new Error(result.message);
      const comment = result.data.comment;
      const previous = feed.querySelector(`[data-comment-id="${Number(comment.id)}"]`);
      previous?.remove();
      feed.prepend(renderComment(comment));
      if (!result.data.updated) total += 1;
      updateRating(result.data.rating);
      message.value = '';
      subject.value = '';
      message.dispatchEvent(new Event('input', { bubbles: true }));
      updateCounts();
      notify('success', result.message);
      // Recaler la pagination et les permissions après une écriture.
      try { await refresh(); }
      catch { notify('error', 'Votre avis est enregistré, mais la liste n’a pas pu être actualisée.'); }
    }, 'Enregistrement de votre commentaire…');
  });
  message.addEventListener('input', () => message.setCustomValidity(''));
  subject.addEventListener('input', () => subject.setCustomValidity(''));

  // Délégation : fonctionne aussi pour les boutons ajoutés après le chargement initial.
  feed.addEventListener('submit', (event) => {
    const deletion = event.target.closest('form');
    if (!deletion || !deletion.querySelector('.comment-delete')) return;
    event.preventDefault();
    if (busy || !window.confirm('Supprimer ce commentaire ?')) return;
    void runAction(async () => {
      const result = await sendJsonForm(deletion);
      if (result.success !== true) throw new Error(result.message);
      feed.querySelector(`[data-comment-id="${Number(result.data.commentId)}"]`)?.remove();
      total = Math.max(0, total - 1);
      updateCounts();
      notify('success', result.message);
      try { await refresh(); }
      catch { notify('error', 'Le commentaire est supprimé, mais la liste n’a pas pu être actualisée.'); }
    }, 'Suppression du commentaire…');
  });

  more.addEventListener('click', (event) => {
    event.preventDefault();
    if (busy) return;
    void runAction(async () => {
      const data = await readPage(page + 1);
      data.comments.forEach((comment) => {
        if (!feed.querySelector(`[data-comment-id="${Number(comment.id)}"]`)) feed.append(renderComment(comment));
      });
      page = data.page;
      total = data.total;
      updateCounts();
      notify('success', 'Les commentaires suivants sont affichés.');
    }, 'Chargement des commentaires…');
  });

  section.querySelectorAll('.comment-sort-menu a').forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      if (busy) return;
      const nextSort = new URL(link.href).searchParams.get('comment_sort') || 'date';
      const oldSort = sort;
      void runAction(async () => {
        sort = nextSort;
        try { await refresh(); } catch (error) { sort = oldSort; throw error; }
        section.querySelectorAll('.comment-sort-menu a').forEach((item) => {
          if (item === link) item.setAttribute('aria-current', 'true');
          else item.removeAttribute('aria-current');
        });
        section.querySelector('.comment-sort').open = false;
        notify('success', 'Le tri des commentaires est actualisé.');
      }, 'Chargement des commentaires…');
    });
  });

  document.addEventListener('auth:change', (event) => {
    memberId = Number(event.detail.user?.id) || 0;
    form.hidden = memberId === 0;
    guestPanel.hidden = memberId !== 0;
    // Retirer immédiatement les contrôles de suppression en attendant les droits relus en base.
    feed.querySelectorAll('.comment-footer-row form').forEach((node) => node.remove());
    if (memberId === 0) { message.value = ''; subject.value = ''; }
    if (busy) { pendingAuthRefresh = true; return; }
    void runAction(async () => { await refresh(); status.textContent = ''; }, 'Actualisation de la session…');
  });

  setBusy(false);
  updateCounts();
})();
