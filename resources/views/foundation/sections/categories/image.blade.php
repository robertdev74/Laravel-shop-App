<div class="modal fade" id="image_{{ $category->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">{{ trans('categories.background') }}</h4>
			</div>
			<div class="modal-body text-center">
				<img src="/{{ $category->image }}?w=350">
			</div>
		</div>
	</div>
</div>
