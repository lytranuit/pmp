<ul>
    <li><a href="#">{{lang("position_diagram")}}</a>

        <ul>
            @foreach($factory as $fac)
            <li>
                <a href="#">
                    {{$fac->name}}
                    <i class="d-block">{{$fac->name_en}}</i>
                </a>
                @if(!empty($fac->child))
                <ul class="d-none">
                    @foreach($fac->child as $workshop)
                    <li>
                        <a href="#">
                            {{$workshop->name}}
                            <i class="d-block">{{$workshop->name_en}}</i>
                        </a>
                        @if(!empty($workshop->child))
                        <ul class="d-none">
                            @foreach($workshop->child as $area)
                            <li>
                                <a href="#">
                                    {{$area->name}}
                                    <i class="d-block">{{$area->name_en}}</i>
                                </a>
                                @if(!empty($area->child))
                                <ul class="d-none">
                                    @foreach($area->child as $room)
                                    <li>
                                        <a href="#">
                                            {{$room->string_id}}
                                        </a>
                                        @if(!empty($room->child))
                                        <ul class="d-none">
                                            @foreach($room->child as $position)
                                            <li>
                                                <a href="#">
                                                    {{$position->string_id}}
                                                </a>
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