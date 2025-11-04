document.addEventListener('DOMContentLoaded', () => {
   const dropdown = document.getElementById('byb_categories_dropdown');
   const chipsContainer = document.querySelector('.byb-category-chips');
   const hiddenInput = document.getElementById('byb_selected_categories_hidden');

   // Add category on dropdown change
   dropdown?.addEventListener('change', (e) => {
      const categoryId = e.target.value;
      const selectedOption = e.target.options[e.target.selectedIndex];
      const categoryName = selectedOption.text;

      if (!categoryId) return;

      // Check if already added
      if (document.querySelector(`.byb-category-chip[data-id="${categoryId}"]`)) {
         e.target.value = '';
         return;
      }

      // Disable the selected option
      selectedOption.disabled = true;

      // Remove empty state
      const emptyState = chipsContainer.querySelector('.byb-empty-state');
      if (emptyState) {
         emptyState.remove();
      }

      // Create chip
      const chip = document.createElement('div');
      chip.className = 'byb-category-chip';
      chip.setAttribute('data-id', categoryId);
      chip.innerHTML = `
                        <span>${categoryName}</span>
                        <span class="remove">×</span>
                        <input type="hidden" name="byb_selected_categories[]" value="${categoryId}">
                    `;

      chipsContainer.appendChild(chip);
      e.target.value = '';

      updateHiddenInput();
   });

   // Remove chip on click (event delegation)
   chipsContainer?.addEventListener('click', (e) => {
      if (e.target.classList.contains('remove')) {
         const chip = e.target.closest('.byb-category-chip');
         const categoryId = chip.getAttribute('data-id');

         // Re-enable the option in dropdown
         const option = dropdown.querySelector(`option[value="${categoryId}"]`);
         if (option) {
            option.disabled = false;
         }

         chip.remove();

         // Show empty state if no chips
         if (!document.querySelector('.byb-category-chip')) {
            const emptyState = document.createElement('div');
            emptyState.className = 'byb-empty-state';
            emptyState.textContent = 'No categories selected. Select from dropdown below to add.';
            chipsContainer.appendChild(emptyState);
         }

         updateHiddenInput();
      }
   });

   const updateHiddenInput = () => {
      const chips = document.querySelectorAll('.byb-category-chip');
      const selectedIds = Array.from(chips).map(chip => chip.getAttribute('data-id'));
      hiddenInput.value = selectedIds.join(',');
   };
});
