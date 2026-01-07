<?php $site_url = 'https://shapoval.agency'; ?>
<script>
    const apiUrl = {
        site: '<?php echo $site_url; ?>/wp-json/acf/v3/pages/10025',
        posts: '<?php echo $site_url; ?>/wp-json/wp/v2/posts?per_page=3&orderby=date&order=desc&status=publish',
        services: '<?php echo $site_url; ?>/wp-json/wp/v2/services?per_page=3&orderby=date&order=desc&status=publish',
        cases: '<?php echo $site_url; ?>/wp-json/wp/v2/cases?per_page=3&orderby=date&order=desc&status=publish'
    };

    function loadData() {
        const cacheTime = 30 * 60 * 1000; // 30 минут

        Object.keys(apiUrl).forEach(function(key) {
            const cacheKey = 'sa_cache_' + key;
            const cacheTsKey = cacheKey + '_ts';
            const cachedData = localStorage.getItem(cacheKey);
            const cachedTs = localStorage.getItem(cacheTsKey);

            if (cachedData && cachedTs && (Date.now() - cachedTs < cacheTime)) {
                displayData(JSON.parse(cachedData), key);
            } else {
                fetch(apiUrl[key])
                    .then(res => res.json())
                    .then(data => {
                        localStorage.setItem(cacheKey, JSON.stringify(data));
                        localStorage.setItem(cacheTsKey, Date.now());
                        displayData(data, key);
                    })
                    .catch(err => console.error('Ошибка загрузки данных:', err));
            }
        });
    }

    function loadImage(media, callback) {
        if (parseInt(media) == media) {
            const imageUrl = '<?php echo $site_url; ?>/wp-json/wp/v2/media/' + media;
            fetch(imageUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Media not found');
                    return response.json();
                })
                .then(data => callback(data.source_url))
                .catch(error => {
                    console.warn('Ошибка изображения:', error);
                    callback('<?php echo plugins_url('assets/img/noimage.png', dirname(__FILE__)); ?>');
                });
        } else if (media) {
            callback(media);
        } else {
            callback('<?php echo plugins_url('assets/img/noimage.png', dirname(__FILE__)); ?>');
        }
    }

    function displayData(data, type) {
        const container = document.getElementById(type + 'Data');
        if (!container) return;

        if (Array.isArray(data)) {
            data.forEach(item => {
                loadImage(item.featured_media, function(imageUrl) {
                    container.innerHTML += `<div class="innerNewsItem">
                        <div class="saNewsItemImg"><img src="${imageUrl}" alt="${item.title.rendered}" width="100"></div>
                        <div class="saNewsItemData">
                            <div class="saNewsItemTitle" title="${item.title.rendered}">${item.title.rendered}</div>
                            <div class="saNewsItemAnons">${item.acf?.anons || ''}</div>
                            <a class="saNewsItemLink" href="${item.link}" target="_blank">Перейти до статті</a>
                        </div>
                    </div>`;
                });
            });
        } else if (type === 'site') {
            loadImage(data.acf?.foto_yaroslava_plugin, function(imageUrl) {
                container.innerHTML = `<div class="pidtrimka">Підтримка вашого проекту</div>
                <div class="yaroslavSa">
                    <img src="${imageUrl}" alt="Шаповал Ярослав">
                </div>
                <div class="yaroslavSaName">Шаповал Ярослав</div>
                <div class="recvData">
                    <div class="iconRecv"><img src="<?php echo plugins_url('assets/img/Icon-tel.svg', dirname(__FILE__)); ?>" alt="Телефон"></div>
                    <div class="resRecv">
                        <span class="telResRecv">${data.acf?.telefon_plugin || ''}</span>
                        <span class="emailResRecv">${data.acf?.email_plugin || ''}</span>
                    </div>
                </div>
                <div class="recvData">
                    <div class="iconRecv"><img src="<?php echo plugins_url('assets/img/Icon-telegram.svg', dirname(__FILE__)); ?>" alt="Telegram"></div>
                    <div class="resRecv">
                        <span class="addrResRecv">${data.acf?.adresa_plugin || ''}</span>
                    </div>
                </div>
                <div class="btnSaBlue"><a class="btnSaBlueLink" href="<?php echo $site_url; ?>" target="_blank">Перейти на сайт</a></div>`;
            });
        }
    }

    document.addEventListener('DOMContentLoaded', loadData);
</script>

<div class="saNewsRow">
    <div id="siteData" class="saNewsItem firstDataSa"></div>
    <div id="postsData" class="saNewsItem"></div>
    <div id="servicesData" class="saNewsItem"></div>
    <div id="casesData" class="saNewsItem"></div>
</div>