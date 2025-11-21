<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      {!! Form::open(['url' => '', 'method' => 'PUT', 'id' => 'category_edit_form']) !!}

      <div class="modal-header">
        <h4 class="modal-title">Edit Category</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        {!! Form::hidden('category_id', null, ['id' => 'edit_category_id']) !!}

        <div class="form-group">
          {!! Form::label('catalog_id', 'Catalog Name:*') !!}
          {!! Form::select('catalog_id', $catalogs ?? [], null, [
                'class' => 'form-control select2-dark-bg', 
                'id' => 'edit_catalog_id', 
                'required', 
                'style' => 'width: 100%;'
          ]) !!}
        </div>

        <div class="form-group">
          {!! Form::label('name', 'Name:*') !!}
          {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'edit_name', 'required']) !!}
        </div>

        <div class="form-group">
          {!! Form::label('description', 'Description:') !!}
          {!! Form::textarea('description', null, ['class' => 'form-control', 'id' => 'edit_description', 'rows' => 3]) !!}
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>

      {!! Form::close() !!}
    </div>
  </div>
</div>
