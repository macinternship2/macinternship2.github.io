@foreach ( $categories as $itemCategory )
	<a href="{{$base_url}}/{{$location->id}}/{{implode('-', explode(" ",strtolower($itemCategory->name)))}}"
	   class="{{$category->name === $itemCategory->name ? 'selected' : ''}}">
		{{$itemCategory->name}}
	</a>
@endforeach