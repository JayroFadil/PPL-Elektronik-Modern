<!-- Mobile Overlay -->
<div id="sidebarOverlayOwner" class="fixed inset-0 bg-dark/60 backdrop-blur-sm z-[9997] hidden transition-opacity duration-300 opacity-0"></div>

<!-- Sidebar Container -->
<div id="ownerSidebar" class="fixed inset-y-0 left-0 z-[9998] w-[280px] bg-dark shrink-0 flex flex-col md:relative md:translate-x-0 -translate-x-full transition-transform duration-300 ease-in-out md:h-screen md:sticky md:top-0 overflow-y-auto border-r border-white/5 shadow-2xl md:shadow-none">
    <div class="p-6 border-b border-white/10 hidden md:block">
        <div class="font-heading text-lg font-extrabold text-white flex items-center gap-2">
            <span class="text-yellow-500 text-xl"><i class="fi fi-rr-crown"></i></span>
            Elektronik Modern
        </div>
        <div class="text-[11px] text-g500 mt-1 uppercase tracking-widest font-bold">Panel Owner</div>
    </div>
    
    {{-- Mobile Sidebar Header (Inside) --}}
    <div class="p-6 border-b border-white/10 md:hidden flex items-center justify-between">
        <div class="font-heading text-lg font-extrabold text-white flex items-center gap-2">
            <span class="text-yellow-500 text-xl"><i class="fi fi-rr-crown"></i></span>
            Menu Owner
        </div>
    </div>

    
    <div class="flex-1">
        <a href="{{ route('owner.index') }}"
            class="flex items-center gap-3 py-3 px-5 text-[13px] font-medium transition-all border-l-[3px] border-transparent no-underline w-full text-left {{ request()->routeIs('owner.index') ? 'text-primary bg-primary/10 border-primary' : 'text-g500 hover:text-white hover:bg-white/5' }}">
            <span class="w-[18px] text-center text-base"><i class="fi fi-rr-chart-histogram"></i></span> Dashboard
        </a>

        {{-- Menu Pengaturan Pengiriman --}}
        <a href="{{ route('owner.pengaturan-pengiriman.index') }}"
            class="flex items-center gap-3 py-3 px-5 text-[13px] font-medium transition-all border-l-[3px] border-transparent no-underline w-full text-left {{ request()->routeIs('owner.pengaturan-pengiriman.*') ? 'text-primary bg-primary/10 border-primary' : 'text-g500 hover:text-white hover:bg-white/5' }}">
            <span class="w-[18px] text-center text-base"><i class="fi fi-rr-truck-side"></i></span>
            Pengaturan Pengiriman
        </a>
    </div>


    <div class="px-5 pt-5 pb-2 text-[10px] font-bold text-g600 tracking-wider uppercase border-t border-white/5">Akun</div>
    <form method="POST" action="{{ route('logout') }}" class="m-0 mb-6">
        @csrf
        <button type="submit" class="flex items-center gap-3 py-3 px-5 text-[13px] font-medium transition-all border-l-[3px] border-transparent no-underline w-full text-left text-g500 hover:text-white hover:bg-white/5 bg-transparent border-none outline-none">
            <span class="w-[18px] text-center text-base"><i class="fi fi-rr-sign-out-alt"></i></span> Keluar
        </button>
    </form>
</div>

<script>
    (function() {
        const setupOwnerSidebar = () => {
            const toggleBtn = document.getElementById('mobileMenuIcon')?.parentElement;
            const sidebarEl = document.getElementById('ownerSidebar');
            const overlayEl = document.getElementById('sidebarOverlayOwner');

            if (!toggleBtn || !sidebarEl || !overlayEl) return;

            const toggleIcon = document.getElementById('mobileMenuIcon');

            const openSidebar = () => {
                sidebarEl.classList.remove('-translate-x-full');
                overlayEl.classList.remove('hidden');
                setTimeout(() => overlayEl.classList.add('opacity-100'), 10);
                if (toggleIcon) {
                    toggleIcon.classList.remove('fi-rr-menu-burger');
                    toggleIcon.classList.add('fi-rr-cross-small');
                }
                document.body.style.overflow = 'hidden';
            };

            const closeSidebar = () => {
                sidebarEl.classList.add('-translate-x-full');
                overlayEl.classList.remove('opacity-100');
                if (toggleIcon) {
                    toggleIcon.classList.remove('fi-rr-cross-small');
                    toggleIcon.classList.add('fi-rr-menu-burger');
                }
                setTimeout(() => {
                    overlayEl.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 300);
            };

            toggleBtn.onclick = (e) => {
                const isOpened = !sidebarEl.classList.contains('-translate-x-full');
                if (isOpened) closeSidebar(); else openSidebar();
            };

            overlayEl.onclick = closeSidebar;
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSidebar(); });
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setupOwnerSidebar);
        else setupOwnerSidebar();
    })();
</script>
