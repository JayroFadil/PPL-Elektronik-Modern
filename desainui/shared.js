// ========== SHARED STATE ==========
const CART = JSON.parse(localStorage.getItem('eh_cart') || '[]');
const NOTIFICATIONS = [
  { id: 1, icon: '<i class="fi fi-rr-checkbox"></i>', title: 'Pesanan Dikonfirmasi', msg: 'Pesanan #ORD-003 sudah diproses admin.', time: '5 menit lalu', read: false },
  { id: 2, icon: '<i class="fi fi-rr-truck"></i>', title: 'Paket Dikirim', msg: 'Pesanan #ORD-002 sudah dikirim via JNE.', time: '2 jam lalu', read: false },
  { id: 3, icon: '<i class="fi fi-rr-check"></i>', title: 'Pesanan Selesai', msg: 'Pesanan #ORD-001 telah diterima.', time: '1 hari lalu', read: true },
  { id: 4, icon: '<i class="fi fi-rr-label"></i>', title: 'Promo Spesial!', msg: 'Diskon 20% untuk Smart TV hari ini saja!', time: '2 hari lalu', read: true },
];

const PRODUCTS = [
  { id: 1, name: 'Samsung Smart TV 43" 4K UHD', cat: 'Smart TV', price: 6499000, oldPrice: 7650000, stock: 12, img: 'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=400&q=80', badge: '−15%' },
  { id: 2, name: 'LG Kulkas 2 Pintu 380L Inverter', cat: 'Kulkas', price: 5199000, oldPrice: 5799000, stock: 8, img: 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=400&q=80', badge: '−10%' },
  { id: 3, name: 'Panasonic Mesin Cuci Front Load 7KG', cat: 'Mesin Cuci', price: 4299000, oldPrice: null, stock: 5, img: 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=400&q=80', badge: null },
  { id: 4, name: 'Daikin AC Split 1PK Inverter 5-Star', cat: 'AC / Pendingin', price: 3850000, oldPrice: 4200000, stock: 20, img: 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=400&q=80', badge: 'HOT' },
  { id: 5, name: 'Sony BRAVIA 55" OLED 4K Google TV', cat: 'Smart TV', price: 14999000, oldPrice: null, stock: 3, img: 'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=400&q=80', badge: null },
  { id: 6, name: 'Sharp Freezer Box 200L', cat: 'Kulkas', price: 2350000, oldPrice: null, stock: 15, img: 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=400&q=80', badge: 'BARU' },
  { id: 7, name: 'Rinnai Kompor Gas 2 Tungku Stainless', cat: 'Kompor & Dapur', price: 689000, oldPrice: null, stock: 34, img: 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&q=80', badge: null },
  { id: 8, name: 'Philips LED Smart Bulb 12W Wi-Fi', cat: 'Penerangan', price: 249000, oldPrice: null, stock: 60, img: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80', badge: null },
];

function saveCart() { localStorage.setItem('eh_cart', JSON.stringify(CART)); }

function addToCart(productId, qty = 1) {
  const prod = PRODUCTS.find(p => p.id === productId);
  if (!prod) return;
  const existing = CART.find(c => c.id === productId);
  if (existing) { existing.qty += qty; } 
  else { CART.push({ ...prod, qty }); }
  saveCart();
  updateCartBadge();
  showCartToast(prod.name);
  if (typeof renderCart === 'function') renderCart();
  if (typeof renderPageCart === 'function') renderPageCart();
}

function removeFromCart(productId) {
  const idx = CART.findIndex(c => c.id === productId);
  if (idx > -1) { CART.splice(idx, 1); saveCart(); }
  updateCartBadge();
  if (typeof renderCart === 'function') renderCart();
  if (typeof renderPageCart === 'function') renderPageCart();
}

function updateQty(productId, delta) {
  const item = CART.find(c => c.id === productId);
  if (!item) return;
  item.qty = Math.max(1, item.qty + delta);
  saveCart();
  if (typeof renderCart === 'function') renderCart();
  if (typeof renderPageCart === 'function') renderPageCart();
}

function cartTotal() { return CART.reduce((s,i) => s + i.price * i.qty, 0); }
  function cartCount() { return CART.reduce((s,i) => s + i.qty, 0); }
function fmt(n) { return 'Rp ' + n.toLocaleString('id-ID'); }

function setBadge(el, count) {
  if (!el) return;
  const value = Number(count) || 0;
  el.textContent = value > 99 ? '99+' : value;
  el.style.display = value > 0 ? 'flex' : 'none';
}

function updateCartBadge() {
  const count = cartCount();
  setBadge(document.getElementById('cartBadgeNav'), count);
  setBadge(document.getElementById('cartBadgeNavMobile'), count);
}

function showCartToast(name) {
  let t = document.getElementById('cart-toast');
  if (!t) { t = document.createElement('div'); t.id='cart-toast'; t.className='cart-toast'; document.body.appendChild(t); }
  t.innerHTML = `<i class="fi fi-rr-shopping-cart" style="margin-right:6px;color:var(--blue)"></i> <strong>${name.substring(0,28)}...</strong> ditambahkan!`;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 3000);
}

function unreadCount() { return NOTIFICATIONS.filter(n => !n.read).length; }
function updateNotifBadge() {
  setBadge(document.getElementById('notifBadgeNav'), unreadCount());
}

// ========== CATEGORIES CRUD ==========
const DEFAULT_CATEGORIES = [
  { id: 1, name: 'Smart TV', icon: 'fi-rr-screen' },
  { id: 2, name: 'Kulkas', icon: 'fi-rr-wind' },
  { id: 3, name: 'Mesin Cuci', icon: 'fi-rr-washer' },
  { id: 4, name: 'AC / Pendingin', icon: 'fi-rr-snowflake' },
  { id: 5, name: 'Kompor & Dapur', icon: 'fi-rr-soup' },
  { id: 6, name: 'Penerangan', icon: 'fi-rr-bulb' }
];

function getCategories() {
  const saved = localStorage.getItem('eh_categories');
  if (!saved || JSON.parse(saved).length === 0) {
    localStorage.setItem('eh_categories', JSON.stringify(DEFAULT_CATEGORIES));
    return DEFAULT_CATEGORIES;
  }
  return JSON.parse(saved);
}

function saveCategories(arr) {
  localStorage.setItem('eh_categories', JSON.stringify(arr));
}

// ========== PROMOS CRUD ==========
function getPromos() {
  const saved = localStorage.getItem('eh_promos');
  if (!saved || JSON.parse(saved).length === 0) {
    const nowStr = new Date().toISOString().slice(0, 16);
    const endStr = new Date(Date.now() + 30 * 24 * 3600 * 1000).toISOString().slice(0, 16);
    const defaultPromos = [
      { id: 1, code: 'VCHR100K', type: 'nominal', value: 100000, quota: 10, start: nowStr, end: endStr },
      { id: 2, code: 'VCHR50K', type: 'nominal', value: 50000, quota: 20, start: nowStr, end: endStr }
    ];
    localStorage.setItem('eh_promos', JSON.stringify(defaultPromos));
    return defaultPromos;
  }
  return JSON.parse(saved);
}

function savePromos(arr) {
  localStorage.setItem('eh_promos', JSON.stringify(arr));
}

// ========== ADDRESS CRUD ==========
const DEFAULT_ADDRESSES = [
  { id:1, label:'Rumah', name:'Budi Santoso', phone:'0812-3456-7890', street:'Jl. Raya Bangkalan No. 45 RT 02/RW 03', province:'Jawa Timur', city:'Bangkalan', district:'Bangkalan', village:'Pejagan', postal:'', isDefault:true },
  { id:2, label:'Kantor', name:'Budi Santoso', phone:'0812-3456-7890', street:'Jl. Trunojoyo No. 1 Kampus UTM', province:'Jawa Timur', city:'Bangkalan', district:'Burneh', village:'Burneh', postal:'', isDefault:false },
];

function getAddresses() {
  const saved = localStorage.getItem('eh_addresses');
  return saved ? JSON.parse(saved) : DEFAULT_ADDRESSES;
}
function saveAddresses(arr) { localStorage.setItem('eh_addresses', JSON.stringify(arr)); }
function getDefaultAddress() { return getAddresses().find(a => a.isDefault) || getAddresses()[0] || null; }

function addAddress(data) {
  const addresses = getAddresses();
  const newAddr = { ...data, id: Date.now() };
  if (newAddr.isDefault || addresses.length === 0) {
    addresses.forEach(a => a.isDefault = false);
    newAddr.isDefault = true;
  }
  addresses.push(newAddr);
  saveAddresses(addresses);
  return newAddr;
}

function updateAddress(id, data) {
  const addresses = getAddresses();
  const idx = addresses.findIndex(a => a.id === id);
  if (idx === -1) return;
  if (data.isDefault) addresses.forEach(a => a.isDefault = false);
  addresses[idx] = { ...addresses[idx], ...data };
  saveAddresses(addresses);
}

function deleteAddress(id) {
  let addresses = getAddresses();
  const wasDefault = addresses.find(a => a.id === id)?.isDefault;
  addresses = addresses.filter(a => a.id !== id);
  if (wasDefault && addresses.length > 0) addresses[0].isDefault = true;
  saveAddresses(addresses);
}

function setDefaultAddress(id) {
  const addresses = getAddresses();
  addresses.forEach(a => a.isDefault = (a.id === id));
  saveAddresses(addresses);
}

// ========== USER PROFILE ==========
const DEFAULT_USER = { name:'Budi Santoso', email:'budi.s@email.com', phone:'0812-3456-7890', avatar:'BS', joined:'November 2024' };
function getUser() { return JSON.parse(localStorage.getItem('eh_user') || JSON.stringify(DEFAULT_USER)); }

// ========== ORDERS CRUD ==========
const DEFAULT_ORDERS = [
  {
    id: 4,
    no_resi: "#ORD-004",
    tanggal_pesan: "06 Des 2024, 18:45",
    status_pesanan: "menunggu",
    ekspedisi: { nama_ekspedisi: "POS Indonesia" },
    ongkos_kirim: 60000,
    subtotal: 14999000,
    diskon: 0,
    total_bayar: 15059000,
    items: [
      {
        name: 'Sony BRAVIA 55" OLED 4K Google TV',
        qty: 1,
        price: 14999000,
        img: 'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=400&q=80'
      }
    ]
  },
  {
    id: 3,
    no_resi: "#ORD-003",
    tanggal_pesan: "05 Des 2024, 11:22",
    status_pesanan: "diproses",
    ekspedisi: { nama_ekspedisi: "J&T Express" },
    ongkos_kirim: 30000,
    subtotal: 4299000,
    diskon: 100000,
    kode_voucher: "VCHR100K",
    total_bayar: 4229000,
    items: [
      {
        name: 'Panasonic Mesin Cuci Front Load 7KG',
        qty: 1,
        price: 4299000,
        img: 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=400&q=80'
      }
    ]
  },
  {
    id: 2,
    no_resi: "#ORD-002",
    tanggal_pesan: "28 Nov 2024, 09:15",
    status_pesanan: "dikirim",
    ekspedisi: { nama_ekspedisi: "JNE Reguler" },
    ongkos_kirim: 45000,
    subtotal: 12899000,
    diskon: 0,
    total_bayar: 12944000,
    items: [
      {
        name: 'LG Kulkas 2 Pintu 380L Inverter',
        qty: 1,
        price: 5199000,
        img: 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=400&q=80'
      },
      {
        name: 'Daikin AC Split 1PK Inverter 5-Star',
        qty: 2,
        price: 3850000,
        img: 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=400&q=80'
      }
    ]
  },
  {
    id: 1,
    no_resi: "#ORD-001",
    tanggal_pesan: "01 Des 2024, 14:30",
    status_pesanan: "selesai",
    ekspedisi: { nama_ekspedisi: "JNE Reguler" },
    ongkos_kirim: 15000,
    subtotal: 6499000,
    diskon: 0,
    total_bayar: 6514000,
    items: [
      {
        name: 'Samsung Smart TV 43" 4K UHD',
        qty: 1,
        price: 6499000,
        img: 'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=400&q=80'
      }
    ]
  }
];

function getOrders() {
  const saved = localStorage.getItem('eh_orders');
  if (!saved) {
    localStorage.setItem('eh_orders', JSON.stringify(DEFAULT_ORDERS));
    return DEFAULT_ORDERS;
  }
  return JSON.parse(saved);
}

function saveOrders(arr) {
  localStorage.setItem('eh_orders', JSON.stringify(arr));
}

// ========== INTERACTIVE LOGIC (matching Laravel) ==========
function toggleMobileMenu() {
  const menu = document.getElementById('mobileMenu');
  const overlay = document.getElementById('mobileMenuOverlay');
  const icon = document.getElementById('mobileMenuIcon');
  if (!menu || !overlay) return;
  const isHidden = !menu.classList.contains('open');
  if (isHidden) {
    menu.classList.add('open');
    overlay.classList.add('open');
    if (icon) { icon.classList.remove('fi-rr-menu-burger'); icon.classList.add('fi-rr-cross-small'); }
    document.body.style.overflow = 'hidden';
  } else {
    menu.classList.remove('open');
    overlay.classList.remove('open');
    if (icon) { icon.classList.remove('fi-rr-cross-small'); icon.classList.add('fi-rr-menu-burger'); }
    document.body.style.overflow = '';
  }
}

function toggleMobileSearch() {
  const bar = document.getElementById('mobileSearchBar');
  if (!bar) return;
  const isHidden = !bar.classList.contains('open');
  if (isHidden) {
    bar.classList.add('open');
    const input = document.getElementById('mobileSearchInput');
    if (input) setTimeout(() => input.focus(), 300);
  } else {
    bar.classList.remove('open');
  }
}

function handleSearch(input, event) {
  if (event.key === 'Enter') {
    window.location.href = 'products.html?q=' + encodeURIComponent(input.value);
  }
}

function toggleNotifPanel() {
  const panel = document.getElementById('notifPanel');
  const overlay = document.getElementById('notifOverlay');
  if (!panel || !overlay) return;
  const isOpen = panel.classList.contains('open');
  if (isOpen) {
    closeNotifPanel();
  } else {
    panel.classList.add('open');
    overlay.classList.add('open');
    renderNotifs();
  }
}

function closeNotifPanel() {
  const panel = document.getElementById('notifPanel');
  const overlay = document.getElementById('notifOverlay');
  if (panel && overlay) {
    panel.classList.remove('open');
    overlay.classList.remove('open');
  }
}

function renderNotifs() {
  const list = document.getElementById('notifList');
  if (!list) return;
  if (!NOTIFICATIONS.length) {
    list.innerHTML = '<div class="p-5 text-slate-400 text-sm text-center">Belum ada notifikasi.</div>';
    return;
  }
  list.innerHTML = NOTIFICATIONS.map(n => `
    <div class="notif-item ${n.read ? '' : 'unread'}" onclick="openNotification(${n.id}, '${n.title}', '${n.msg}')">
      <div class="notif-icon">${n.icon}</div>
      <div style="flex:1;min-width:0">
        <div class="notif-title">${n.title}</div>
        <div class="notif-msg">${n.msg}</div>
        <div class="notif-time"><i class="fi fi-rr-clock-three"></i> ${n.time}</div>
      </div>
    </div>
  `).join('');
  updateNotifBadge();
}

function markNotificationsRead() {
  NOTIFICATIONS.forEach(n => n.read = true);
  renderNotifs();
  updateNotifBadge();
}

function openNotification(id, title, msg) {
  const notif = NOTIFICATIONS.find(n => n.id === id);
  if (notif) notif.read = true;
  renderNotifs();
  
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      title: title,
      text: msg,
      icon: 'info',
      confirmButtonColor: '#1A5CFF',
      confirmButtonText: 'Tutup'
    });
  } else {
    alert(title + '\n\n' + msg);
  }
}

// Initialize reveal trigger
document.addEventListener('DOMContentLoaded', () => {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('active');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  document.querySelectorAll('.reveal, .reveal-group').forEach(el => observer.observe(el));
  
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', toggleMobileMenu);
  }

  updateCartBadge();
  updateNotifBadge();
});
