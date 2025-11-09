export function lazyLoad(src, isModule = false) {
    return new Promise((resolve, reject) => {

        // Daha önce yüklenmiş mi?
        if (document.querySelector(`script[src^="${src}"]`)) {
            resolve();
            return;
        }

        const s = document.createElement("script");
        s.src = src + "?v=" + Date.now();
        if (isModule) s.type = "module";

        s.onload = () => resolve();
        s.onerror = () => reject(`❌ Script yüklenemedi: ${src}`);
        document.head.appendChild(s);
    });
}
