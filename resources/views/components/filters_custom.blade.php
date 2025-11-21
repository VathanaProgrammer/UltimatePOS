<style>
.filter-toggle {
    display: none;
}
.filter-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease;
}
.filter-toggle:checked ~ .filter-body {
    max-height: 500px;
}
</style>

<div class="custom-filter tw-bg-white tw-rounded-xl tw-shadow-sm p-3 mb-4">
    
    <input type="checkbox" id="filterToggle" class="filter-toggle">
    
    <label for="filterToggle" class="tw-flex tw-justify-between tw-items-center tw-cursor-pointer">
        <h3 class="tw-text-lg tw-font-medium tw-select-none">
            <i class="fa fa-filter"></i> {{ $title ?? 'Filters' }}
        </h3>
        <i class="fa fa-chevron-down"></i>
    </label>

    <div class="filter-body">
        {{ $slot }}
    </div>

</div>
