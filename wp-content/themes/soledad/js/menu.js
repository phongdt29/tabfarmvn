class ResponsiveMenuHandler {
  constructor(menuSelector) {
    this.menu = document.querySelector(menuSelector);
    if (!this.menu) {
      //console.error('Menu not found with selector:', menuSelector);
      return;
    }
    
    // Check if menu itself is a UL, or find UL inside it
    this.menuList = this.menu.tagName === 'UL' ? this.menu : this.menu.querySelector('ul');
    
    if (!this.menuList) {
      //console.error('Menu list (ul) not found in:', menuSelector);
      return;
    }
    
    this.originalItems = [];
    this.moreDropdown = null;
    this.resizeTimeout = null;
    
    this.init();
  }
  
  init() {
    // Store original menu items
    this.storeOriginalItems();
    
    // Create "More" dropdown
    this.createMoreDropdown();
    
    // Initial check
    this.checkOverflow();
    
    // Add resize listener with debounce
    window.addEventListener('resize', () => {
      clearTimeout(this.resizeTimeout);
      this.resizeTimeout = setTimeout(() => {
        this.checkOverflow();
      }, 150);
    });
  }
  
  storeOriginalItems() {
    const items = Array.from(this.menuList.children);
    this.originalItems = items.map(item => ({
      element: item.cloneNode(true),
      width: item.offsetWidth
    }));
  }
  
  createMoreDropdown() {
    this.moreDropdown = document.createElement('li');
    this.moreDropdown.className = 'menu-item menu-item-has-children menu-more-dropdown';
    this.moreDropdown.style.display = 'none';
    this.moreDropdown.innerHTML = `
      <a href="#">More</a>
      <ul class="sub-menu"></ul>
    `;
    this.menuList.appendChild(this.moreDropdown);
  }
  
  checkOverflow() {
    // Reset: move all items back to main menu
    this.resetMenu();
    
    // Wait for layout to settle after reset
    requestAnimationFrame(() => {
      const menuWidth = this.menuList.offsetWidth;
      const moreDropdownWidth = 120; // Width for "More" dropdown
      
      const items = Array.from(this.menuList.children).filter(
        item => !item.classList.contains('menu-more-dropdown')
      );
      
      // Check if menu is wrapping to multiple lines
      if (items.length === 0) return;
      
      const firstItemTop = items[0].offsetTop;
      let overflowItems = [];
      let totalWidth = 0;
      
      // First pass: check if items are wrapping
      let hasWrapped = false;
      items.forEach(item => {
        if (item.offsetTop > firstItemTop) {
          hasWrapped = true;
        }
      });
      
      // If wrapped, calculate which items to move
      if (hasWrapped) {
        // Calculate available width (accounting for More dropdown)
        const availableWidth = menuWidth - moreDropdownWidth;
        
        items.forEach((item, index) => {
          const itemWidth = item.offsetWidth + parseInt(getComputedStyle(item).marginLeft) + parseInt(getComputedStyle(item).marginRight);
          totalWidth += itemWidth;
          
          // If we exceed available width, move this and remaining items to dropdown
          if (totalWidth > availableWidth) {
            overflowItems.push(item);
          }
        });
        
        // If we have overflow items, show dropdown
        if (overflowItems.length > 0) {
          this.showMoreDropdown(overflowItems);
          
          // Double-check if still wrapping after moving items
          setTimeout(() => {
            const remainingItems = Array.from(this.menuList.children).filter(
              item => !item.classList.contains('menu-more-dropdown') && item.style.display !== 'none'
            );
            
            if (remainingItems.length > 0) {
              const newFirstTop = remainingItems[0].offsetTop;
              let stillWrapping = false;
              
              remainingItems.forEach(item => {
                if (item.offsetTop > newFirstTop) {
                  stillWrapping = true;
                }
              });
              
              // If still wrapping, move one more item
              if (stillWrapping && overflowItems.length < items.length - 1) {
                this.checkOverflow();
              }
            }
          }, 10);
        }
      } else {
        this.hideMoreDropdown();
      }
    });
  }
  
  showMoreDropdown(items) {
    const dropdownMenu = this.moreDropdown.querySelector('.sub-menu');
    dropdownMenu.innerHTML = '';
    
    items.forEach(item => {
      const clonedItem = item.cloneNode(true);
      // Remove any sub-menu indicators if it's a top-level item
      clonedItem.classList.remove('current-menu-item', 'current-menu-parent', 'current-menu-ancestor');
      dropdownMenu.appendChild(clonedItem);
      item.style.display = 'none';
    });
    
    this.moreDropdown.style.display = '';
  }
  
  hideMoreDropdown() {
    this.moreDropdown.style.display = 'none';
    
    // Show all hidden items
    const items = Array.from(this.menuList.children).filter(
      item => !item.classList.contains('menu-more-dropdown')
    );
    items.forEach(item => {
      item.style.display = '';
    });
  }
  
  resetMenu() {
    // Show all items
    const items = Array.from(this.menuList.children).filter(
      item => !item.classList.contains('menu-more-dropdown')
    );
    items.forEach(item => {
      item.style.display = '';
    });
    
    // Clear dropdown
    const dropdownMenu = this.moreDropdown.querySelector('.sub-menu');
    dropdownMenu.innerHTML = '';
    this.moreDropdown.style.display = 'none';
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // Initialize for all menus with .navigation class (excluding vertical)
  const menus = document.querySelectorAll('.navigation:not(.navigation--vertical)');
  
  menus.forEach((menu, index) => {
    // Add unique identifier if not present
    if (!menu.id) {
      menu.id = `navigation-${index}`;
    }
    
    // Initialize handler for each menu
    new ResponsiveMenuHandler(`#${menu.id}`);
  });
});