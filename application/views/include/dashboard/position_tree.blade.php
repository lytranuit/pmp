<ul>
    <li><a href="#">Position diagram</a>

        <ul>
            @foreach($factory as $fac)
            <li>
                <a href="#">{{$fac->name}}</a>
                @if(!empty($fac->child))
                <ul class="">
                    @foreach($fac->child as $workshop)
                    <li>
                        <a href="#">{{$workshop->name}}</a>
                        @if(!empty($workshop->child))
                        <ul class="d-none">
                            @foreach($workshop->child as $area)
                            <li>
                                <a href="#">{{$area->name}}</a>
                                @if(!empty($area->child))
                                <ul class="d-none">
                                    @foreach($area->child as $room)
                                    <li>
                                        <a href="#">{{$room->string_id}}</a>
                                        @if(!empty($room->child))
                                        <ul class="d-none">
                                            @foreach($room->child as $position)
                                            <li>
                                                <a href="#">{{$position->string_id}}</a>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </li>

                                    @endforeach
                                </ul>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif
            </li>
            @endforeach
        </ul>
    </li>


</ul>