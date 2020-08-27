<ul id="progressbar" class="text-center w-100 row">
    <div class="connecting-line"></div>
    <li class="col" name="step1">
        <a class="box {{ $type == 'factory' ? 'bg-warning' : '' }}" href="{{base_url()}}factory/">Factory</a>
    </li>
    <li class="col" name="step2">
        <a class="box {{ $type == 'workshop' ? 'bg-warning' : '' }}" href="{{base_url()}}workshop/">Department</a>
    </li>
    <li class="col" name="step3">
        <a class="box {{ $type == 'area' ? 'bg-warning' : '' }}" href="{{base_url()}}area/">Area</a>
    </li>
    <li class="col" name="step3">
        <a class="box {{ $type == 'department' ? 'bg-warning' : '' }}" href="{{base_url()}}department/">Room/Equipment</a>
    </li>
    <li class="col" name="step3">
        <a class="box {{ $type == 'position' ? 'bg-warning' : '' }}" href="{{base_url()}}position/">Position</a>
    </li>
</ul>
<style>
    .connecting-line {
        height: 2px;
        background: #e0e0e0;
        position: absolute;
        width: 80%;
        margin: 0 auto;
        left: 0;
        right: 0;
        top: 50%;
    }

    #progressbar {
        position: relative;
        margin-bottom: 30px;
        overflow: hidden;
        color: #455A64;
        padding-left: 0px;
        margin-top: 30px
    }

    #progressbar li {
        list-style-type: none;
        float: left;
        position: relative;
        font-weight: 400
    }


    #progressbar li .box {
        display: inline-block;
        padding: 10px 30px;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: #455A64;
        /* border-radius: 50%; */
        margin: auto;
        color: #fff;
    }
</style>