document.addEventListener('DOMContentLoaded', function(){

    const openBtn  = document.getElementById('openSidebar');
    const closeBtn = document.getElementById('closeSidebar');

    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if(openBtn){
        openBtn.addEventListener('click', function(){

            sidebar.classList.add('mobile-open');
            overlay.classList.add('show');

        });
    }

    if(closeBtn){
        closeBtn.addEventListener('click', function(){

            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');

        });
    }

    if(overlay){
        overlay.addEventListener('click', function(){

            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');

        });
    }

});
