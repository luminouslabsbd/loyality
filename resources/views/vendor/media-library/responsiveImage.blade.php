<img{!! $attributeString !!}@if ($loadingAttributeValue) loading="{{ $loadingAttributeValue }}" @endif
    srcset="{{ $media->getSrcset($conversion) }}" src="{{ $media->getUrl($conversion) }}" width="{{ $width }}"
    height="{{ $height }}">
