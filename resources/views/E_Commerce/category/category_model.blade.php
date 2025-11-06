<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      {!! Form::open(['route' => 'category_e.store', 'method' => 'post', 'id' => 'category_add_form']) !!}

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Add Category</h4>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="form-group col-sm-12">
            {!! Form::label('catalog_id', 'Catalog Name:*') !!}
            {!! Form::select(
                'catalog_id',
                $catalogs ?? [],
                null,
                ['class' => 'form-control select2-dark-bg', 'placeholder' => 'Choose a catalog', 'required', 'style' => 'width: 100%;']
            ) !!}
          </div>

          <div class="form-group col-sm-12">
            {!! Form::label('name', 'Name:*') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Category Name', 'required']) !!}
          </div>

          <div class="form-group col-sm-12">
            {!! Form::label('description', 'Description:') !!}
            {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Description']) !!}
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>

      {!! Form::close() !!}
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Initialize Select2 with dark background only
  $('#categoryModal .select2-dark-bg').select2({
    width: '100%',
    dropdownCssClass: 'select2-dropdown-dark-bg'
  });
</script>

<style>
  /* Dark dropdown background but normal text */
  .select2-dropdown-dark-bg {
    background-color: #1f2937 !important; /* dark gray */
    color: #111827 !important; /* normal dark text */
  }

  /* Search field inside dropdown */
  .select2-dropdown-dark-bg .select2-search__field {
    background-color: #1f2937 !important;
    color: #111827 !important; /* normal text */
  }
</style>
@endpush
