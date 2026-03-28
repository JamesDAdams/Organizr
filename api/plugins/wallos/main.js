console.info('Wallos plugin main.js loaded');

function wallosIsInCurrentMonth(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    return (date.getFullYear() === now.getFullYear() && date.getMonth() === now.getMonth());
}

function wallosFormatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
}

function wallosCreateCard(sub, wallosUrl) {
    const logoValue = sub.logo || '';
    const isDataLogo = logoValue.startsWith('data:image/');
    const logoFile = !isDataLogo && logoValue ? encodeURIComponent(logoValue) : '';
    const proxyLogoUrl = isDataLogo ? logoValue : (logoFile ? `api/v2/plugins/wallos/logo?file=${logoFile}` : 'plugins/images/wallos.png');
    const directLogoUrl = !isDataLogo && logoFile ? `${wallosUrl}/images/uploads/logos/${logoFile}` : 'plugins/images/wallos.png';
    return `
    <div style="width: 150px; cursor: pointer; transition: transform 0.15s ease-out; font-family: 'Open Sans', 'Segoe UI', sans-serif;"
         onmouseover="this.style.transform='scale(1.04)';" onmouseout="this.style.transform='scale(1)';">
        <div style="position: relative; width: 100%; aspect-ratio: 2 / 3; background: #2d2d2d; border-radius: 4px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.4); margin-bottom: 8px; display: flex; align-items: center; justify-content: center;">
            <img src="${proxyLogoUrl}"
                 onerror="this.onerror=null; this.src='${directLogoUrl}';"
                 style="width: 80%; height: 80%; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4));">
            <div style="position: absolute; top: 0; right: 0; background: #e5a00d; color: #000; font-weight: 700; font-size: 11px; padding: 2px 6px; border-bottom-left-radius: 4px;">
                ${parseFloat(sub.price).toFixed(2)}€
            </div>
        </div>
        <div style="padding: 0 2px;">
            <div style="color: #eee; font-size: 13px; font-weight: 400; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 1px;">${sub.name}</div>
            <div style="color: #999; font-size: 11px;">${wallosFormatDate(sub.next_payment)} • ${sub.payment_method_name || ''}</div>
        </div>
    </div>`;
}

window.wallosLoadSubscriptions = async function () {
    const container = document.getElementById('wallos-subscriptions-container');
    console.info('wallosLoadSubscriptions container exists:', !!container);
    if (!container) {
        console.info('wallosLoadSubscriptions: container not found, aborting');
        return;
    }

    try {
        const fetchUrl = 'api/v2/plugins/wallos/subscriptions';
        console.info('Wallos: fetching', fetchUrl);
        const response = await fetch(fetchUrl, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });
        console.info('Wallos fetch response status:', response.status, response.statusText);
        const res = await response.json();
        console.info('Wallos fetch json:', res);

        if (response.ok && res.response?.data) {
            const data = res.response.data;
            const subscriptions = Array.isArray(data.subscriptions) ? data.subscriptions : [];
            const wallosUrl = data.url;

            if (subscriptions.length > 0) {
                const filtered = subscriptions.filter(sub => wallosIsInCurrentMonth(sub.next_payment));
                if (filtered.length === 0) {
                    container.innerHTML = '<div style="color:#666; font-size: 14px; margin-left: 10px;">Aucun paiement prévu ce mois-ci.</div>';
                } else {
                    container.innerHTML = filtered.map(sub => wallosCreateCard(sub, wallosUrl)).join('');
                }
            } else {
                container.innerHTML = '<div style="color:#666; font-size: 14px; margin-left: 10px;">Aucun abonnement trouvé.</div>';
            }
        } else {
            console.warn('Wallos: unexpected fetch response', res);
            container.innerHTML = '<div style="color:#f44336; font-size: 14px;">Erreur de chargement.</div>';
        }
    } catch (e) {
        console.error('Error Wallos API:', e);
        container.innerHTML = '<div style="color:#f44336; font-size: 14px;">Erreur réseau.</div>';
    }
}

// Initial load handler
setTimeout(() => {
    console.info('Wallos: initial setTimeout checking for container');
    if (document.getElementById('wallos-subscriptions-container')) {
        window.wallosLoadSubscriptions();
    }
}, 1000);
