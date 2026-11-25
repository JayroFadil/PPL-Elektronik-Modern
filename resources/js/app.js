// import './bootstrap';

// Global Helpers
window.setBadge = function(el, count) {
    if (!el) return;
    const value = Number(count) || 0;
    el.textContent = value > 99 ? '99+' : value;
    el.style.display = value > 0 ? 'flex' : 'none';
};

// Intersection Observer for Reveal Animations
const initRevealAnimations = () => {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal, .reveal-group').forEach(el => observer.observe(el));
};

// SweetAlert Helpers
const initSweetAlert = () => {
    if (typeof Swal === 'undefined') return;

    window.Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    window.showAlert = (icon, title, text) => {
        Swal.fire({
            icon: icon,
            title: title,
            html: text,
            confirmButtonColor: '#1A5CFF',
        });
    };

    window.showToast = (icon, title) => {
        window.Toast.fire({
            icon: icon,
            title: title
        });
    };

    window.showPaymentSuccessAlert = (title, text, callback) =>{
        Swal.fire({
            title: title||'Pembayaran Berhasil!',
            html: text||'Terima kasih, pembayaran pesanan Anda telah kami terima.',
            imageUrl:'./public/image/wokee-danamasuk.png',
            imageWidth: 150,
            imageHeight: 150,
            imageAlt: 'Pembayaran Berhasil',
            confirmButtonColor:'#1A5CFF',
            confirmButtonText:'Lihat Pesanan',
        }).then(()=>{if (callback)callback();
        });
    };
};

// Initialize immediately so global functions are available
initSweetAlert();

// Wishlist Logic
window.promptLogin = function() {
    if (typeof Swal === 'undefined') {
        window.location = window.AppConfig.routes.login;
        return;
    }

    Swal.fire({
        icon: 'info',
        title: 'Masuk Terlebih Dahulu',
        text: 'Silakan masuk ke akun Anda untuk dapat menggunakan fitur ini.',
        showCancelButton: true,
        confirmButtonText: 'Masuk Sekarang',
        cancelButtonText: 'Nanti',
        confirmButtonColor: '#1A5CFF',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = window.AppConfig.routes.login;
        }
    });
};

window.toggleWishlist = function(btn, id_produk, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (!window.AppConfig.auth.check) {
        window.promptLogin();
        return;
    }

    fetch(window.AppConfig.routes.wishlistToggle, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.AppConfig.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id_produk: id_produk })
    })
    .then(response => {
        if (response.status === 401) {
            window.promptLogin();
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && typeof data.liked !== 'undefined') {
            if (data.liked) {
                btn.classList.add('text-red-500');
                btn.classList.remove('text-slate-400');
                btn.innerHTML = '<i class="fi fi-sr-heart"></i>';
            } else {
                btn.classList.remove('text-red-500');
                btn.classList.add('text-slate-400');
                btn.innerHTML = '<i class="fi fi-rr-heart"></i>';
            }
            if (typeof refreshWishlistBadge === 'function') {
                refreshWishlistBadge();
            }
        }
    })
    .catch(() => {});
};

window.refreshWishlistBadge = function() {
    if (!window.AppConfig.auth.check) return;
    fetch(window.AppConfig.routes.wishlistCount)
        .then(response => response.json())
        .then(data => {
            setBadge(document.getElementById('wishlistBadgeNav'), data.wishlistCount);
        })
        .catch(() => {});
};

window.refreshCartBadge = function() {
    if (!window.AppConfig.auth.check) return;
    fetch(window.AppConfig.routes.cartCount)
        .then(response => response.json())
        .then(data => {
            setBadge(document.getElementById('cartBadgeNav'), data.cartCount);
        })
        .catch(() => {});
};

// Cart Actions
window.addToCart = function(btn, id_produk, qty = 1, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    if (!window.AppConfig.auth.check) {
        window.promptLogin();
        return;
    }

    btn.disabled = true;
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i>';

    fetch(window.AppConfig.routes.cartAdd, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.AppConfig.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id_produk: id_produk, qty: qty })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = oldHtml;
        if (data.status) {
            if (data.cartCount !== undefined) {
                setBadge(document.getElementById('cartBadgeNav'), data.cartCount);
            } else {
                refreshCartBadge();
            }
            if (typeof window.showToast === 'function') {
                window.showToast('success', data.message || 'Berhasil ditambahkan');
            }
        } else {
            if (typeof window.showAlert === 'function') {
                window.showAlert('error', 'Gagal', data.message || 'Gagal menambahkan ke keranjang');
            }
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = oldHtml;
    });
};

// Notifications
window.loadNotifications = function() {
    if (!window.AppConfig.auth.check) return;
    const notifList = document.getElementById('notifList');
    if(!notifList) return;

    fetch(window.AppConfig.routes.notifications)
        .then(response => response.json())
        .then(data => {
            setBadge(document.getElementById('notifBadgeNav'), data.unreadCount);
            if (!data.notifications.length) {
                notifList.innerHTML = '<div class="p-5 text-slate-400 text-sm text-center">Belum ada notifikasi.</div>';
                return;
            }

            notifList.innerHTML = data.notifications.map(item => {
                const safePesan = item.pesan.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const safeJudul = item.judul.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const isRead = item.is_read;
                
                return `
                    <div id="notif-${item.id}" 
                        onclick="window.openNotification(${item.id}, '${safeJudul}', '${safePesan}', event)"
                        class="flex gap-4 p-5 cursor-pointer transition-all duration-300 relative group ${isRead ? 'hover:bg-slate-50' : 'bg-blue-50/40 hover:bg-blue-50'}">
                        
                        <div class="w-11 h-11 rounded-2xl bg-white shadow-sm border border-slate-100 flex items-center justify-center text-xl shrink-0 transition-transform group-hover:scale-110 duration-300">
                            ${item.ikon}
                        </div>
                        
                        <div class="flex-1 min-w-0 pr-6">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[13px] font-extrabold text-slate-800 truncate">${item.judul}</span>
                                ${!isRead ? '<span class="w-2 h-2 rounded-full bg-primary animate-pulse shrink-0"></span>' : ''}
                            </div>
                            <p class="text-[12px] text-slate-500 leading-relaxed line-clamp-2 mb-2 group-hover:text-slate-700 transition-colors">${item.pesan}</p>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fi fi-rr-clock-three text-[12px]"></i> ${item.created_at || 'Baru saja'}
                            </span>
                        </div>
                        
                        <button onclick="window.deleteNotification(${item.id}, event)" 
                            class="absolute right-4 top-1/2 -translate-y-1/2 w-9 h-9 rounded-xl bg-white border border-slate-100 text-slate-400 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center hover:bg-red-500 hover:text-white hover:border-red-500 shadow-sm translate-x-2 group-hover:translate-x-0">
                            <i class="fi fi-rr-trash text-xs"></i>
                        </button>
                    </div>
                `;
            }).join('');
        })
        .catch(() => {
            notifList.innerHTML = '<div class="p-5 text-red-500 text-sm text-center">Gagal memuat notifikasi.</div>';
        });
};

window.openNotification = function(id, title, message, event) {
    if (event) {
        // If we clicked the delete button, don't open the modal
        if (event.target.closest('button')) return;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: message,
            icon: 'info',
            confirmButtonColor: '#1A5CFF',
            confirmButtonText: 'Tutup'
        });
    }

    const url = window.AppConfig.routes.notificationsRead.replace(':id', id);
    fetch(url, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': window.AppConfig.csrfToken,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            const el = document.getElementById(`notif-${id}`);
            if (el) {
                el.classList.remove('bg-blue-50');
                const dot = el.querySelector('.bg-blue-600');
                if (dot) dot.remove();
            }
            setBadge(document.getElementById('notifBadgeNav'), data.unreadCount);
        }
    })
    .catch(() => {});
};

window.deleteNotification = function(id, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const url = window.AppConfig.routes.notificationsDestroy.replace(':id', id);

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': window.AppConfig.csrfToken,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            const el = document.getElementById(`notif-${id}`);
            if (el) {
                el.classList.add('opacity-0', '-translate-x-5');
                setTimeout(() => {
                    el.remove();
                    setBadge(document.getElementById('notifBadgeNav'), data.unreadCount);
                    if (document.querySelectorAll('#notifList > div').length === 0) {
                        loadNotifications();
                    }
                }, 300);
            }
        }
    })
    .catch(() => {});
};

window.toggleNotifPanel = function() {
    const notifPanel = document.getElementById('notifPanel');
    const notifOverlay = document.getElementById('notifOverlay');
    if (!notifPanel || !notifOverlay) return;
    
    const isOpen = notifPanel.classList.contains('opacity-100');
    if (isOpen) {
        closeNotifPanel();
    } else {
        notifPanel.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-2', 'scale-95');
        notifPanel.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0', 'scale-100');
        notifOverlay.classList.remove('hidden');
        loadNotifications();
    }
};

window.closeNotifPanel = function() {
    const notifPanel = document.getElementById('notifPanel');
    const notifOverlay = document.getElementById('notifOverlay');
    if (!notifPanel || !notifOverlay) return;
    
    notifPanel.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2', 'scale-95');
    notifPanel.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0', 'scale-100');
    notifOverlay.classList.add('hidden');
};

window.markNotificationsRead = function() {
    fetch(window.AppConfig.routes.notificationsReadAll, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': window.AppConfig.csrfToken,
            'Accept': 'application/json',
        },
    }).then(() => loadNotifications());
};

// Search handling
window.handleSearch = function(input, event) {
    if(event.key === 'Enter') {
        window.location = '/products?q=' + encodeURIComponent(input.value);
    }
};

window.toggleMobileSearch = function() {
    const bar = document.getElementById('mobileSearchBar');
    if (!bar) return;
    
    const isHidden = bar.classList.contains('translate-y-[-100%]');
    if (isHidden) {
        bar.classList.remove('translate-y-[-100%]');
        bar.classList.add('translate-y-0');
        const input = document.getElementById('mobileSearchInput');
        if (input) setTimeout(() => input.focus(), 300);
    } else {
        bar.classList.add('translate-y-[-100%]');
        bar.classList.remove('translate-y-0');
    }
};

// Detail Page Logic
window.changeQty = function(delta) {
    const input = document.getElementById('qtyInput');
    const hidden = document.getElementById('qtyHidden');
    if (!input || !hidden) return;
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(val, parseInt(input.max) || Infinity));
    input.value = val;
    hidden.value = val;
};

window.toggleWishlistDetail = function(btn, id_produk) {
    if (!window.AppConfig.auth.check) {
        window.promptLogin();
        return;
    }

    fetch(window.AppConfig.routes.wishlistToggle, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.AppConfig.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id_produk: id_produk })
    })
    .then(response => {
        if (response.status === 401) {
            window.promptLogin();
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && typeof data.liked !== 'undefined') {
            const icon = btn.querySelector('i');
            const text = btn.querySelector('.wishlist-text');
            if (data.liked) {
                btn.classList.add('bg-red-50', 'border-red-200', 'text-red-500');
                btn.classList.remove('bg-white', 'border-g300', 'text-g700');
                if(icon) icon.className = 'fi fi-sr-heart';
                if(text) text.textContent = 'Hapus dari Wishlist';
            } else {
                btn.classList.remove('bg-red-50', 'border-red-200', 'text-red-500');
                btn.classList.add('bg-white', 'border-g300', 'text-g700');
                if(icon) icon.className = 'fi fi-rr-heart';
                if(text) text.textContent = 'Tambah ke Wishlist';
            }
            if (typeof refreshWishlistBadge === 'function') {
                refreshWishlistBadge();
            }
        }
    })
    .catch(() => {});
};

// Checkout Page Logic
window.selectPay = function(el, type) {
    document.querySelectorAll('.pay-opt').forEach(o => {
        o.classList.remove('border-primary', 'bg-primary-light');
        o.classList.add('border-g200');
    });
    el.classList.remove('border-g200');
    el.classList.add('border-primary', 'bg-primary-light');
    
    const input = el.querySelector('input');
    if (input) input.checked = true;
    
    const bankDetail = document.getElementById('bankDetail');
    const ewalletDetail = document.getElementById('ewalletDetail');
    
    if (type === 'bank') {
        if(bankDetail) bankDetail.classList.remove('hidden');
        if(ewalletDetail) ewalletDetail.classList.add('hidden');
    } else {
        if(bankDetail) bankDetail.classList.add('hidden');
        if(ewalletDetail) ewalletDetail.classList.remove('hidden');
    }
};

window.handleUpload = function(inp) {
    const preview = document.getElementById('uploadPreview');
    const msg = document.getElementById('uploadPreviewMsg');
    if (inp.files && inp.files.length > 0 && preview && msg) {
        preview.classList.remove('hidden');
        msg.textContent = 'File terpilih: ' + inp.files[0].name;
    }
};

window.switchTab = function(tabId, el, event) {
    if (event) event.preventDefault();
    document.querySelectorAll('.profile-nav a').forEach(a => {
        a.classList.remove('bg-primary-light', 'text-primary');
        a.classList.add('text-g600');
    });
    el.classList.remove('text-g600');
    el.classList.add('bg-primary-light', 'text-primary');
    
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    const targetPanel = document.getElementById(tabId);
    if (targetPanel) targetPanel.classList.remove('hidden');
};

window.removeFromWishlist = function(id_produk) {
    if (!window.AppConfig.auth.check) {
        window.promptLogin();
        return;
    }

    fetch(window.AppConfig.routes.wishlistToggle, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.AppConfig.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id_produk: id_produk })
    })
    .then(response => response.json())
    .then(data => {
        if (data && typeof data.liked !== 'undefined' && !data.liked) {
            const card = document.getElementById(`card-${id_produk}`);
            if (card) {
                card.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    card.remove();
                    // Check if empty
                    const remainingCards = document.querySelectorAll('.wishlist-card');
                    if (remainingCards.length === 0) {
                        document.getElementById('wishlistContainer').innerHTML = `
                            <div class="bg-white rounded-2xl shadow-sm border border-g100 p-12 text-center mt-5">
                                <div class="text-[64px] text-g300 mb-4 inline-flex justify-center"><i class="fi fi-rr-heart"></i></div>
                                <h3 class="font-heading text-xl font-extrabold text-g800 mb-2">Wishlist Anda Kosong</h3>
                                <p class="text-g500 text-sm mb-6 max-w-sm mx-auto">Simpan produk-produk impian Anda di sini untuk dibeli nanti.</p>
                                <a href="${window.AppConfig.routes.products}" class="inline-flex py-3 px-6 bg-primary text-white rounded-full font-bold text-[15px] shadow-[0_4px_12px_rgba(26,92,255,0.3)] hover:bg-primary-dark hover:-translate-y-px transition-all items-center gap-2">
                                    <i class="fi fi-rr-search"></i> Cari Produk Favorit
                                </a>
                            </div>
                        `;
                    }
                }, 300);
            }
            if (typeof refreshWishlistBadge === 'function') {
                refreshWishlistBadge();
            }
        }
    })
    .catch(() => {});
};

window.confirmDelete = function(form, message = 'Data yang dihapus tidak dapat dikembalikan!') {
    if (typeof Swal === 'undefined') {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            form.submit();
        }
        return;
    }

    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
};

window.confirmCompleteOrder = function(form) {
    if (typeof Swal === 'undefined') {
        if (confirm('Apakah Anda yakin pesanan sudah diterima dan ingin menyelesaikan pesanan ini?')) {
            form.submit();
        }
        return;
    }

    Swal.fire({
        title: 'Selesaikan Pesanan?',
        text: 'Pastikan Anda telah menerima produk dengan baik. Pesanan yang sudah selesai tidak dapat diubah kembali.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1A5CFF', // primary color matching theme
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Selesaikan',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
};

// Mobile Menu
window.toggleMobileMenu = function() {
    const menu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    const icon = document.getElementById('mobileMenuIcon');
    if (!menu || !overlay) return;
    
    const isHidden = menu.classList.contains('-translate-x-full');
    if (isHidden) {
        menu.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        if (icon) { icon.classList.remove('fi-rr-menu-burger'); icon.classList.add('fi-rr-cross-small'); }
        setTimeout(() => overlay.classList.add('opacity-100'), 10);
        document.body.style.overflow = 'hidden';
    } else {
        menu.classList.add('-translate-x-full');
        overlay.classList.remove('opacity-100');
        if (icon) { icon.classList.remove('fi-rr-cross-small'); icon.classList.add('fi-rr-menu-burger'); }
        setTimeout(() => overlay.classList.add('hidden'), 300);
        document.body.style.overflow = '';
    }
};

document.addEventListener('DOMContentLoaded', () => {
    initRevealAnimations();
    // initSweetAlert(); // Already initialized above

    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', window.toggleMobileMenu);
    }

    // Initial badges
    if (window.AppConfig.auth.check) {
        setBadge(document.getElementById('cartBadgeNav'), window.AppConfig.auth.cartQty);
        setBadge(document.getElementById('wishlistBadgeNav'), window.AppConfig.auth.wishlistCount);
        if ('requestIdleCallback' in window) {
            requestIdleCallback(loadNotifications);
        } else {
            window.addEventListener('load', loadNotifications);
        }
    }
});
