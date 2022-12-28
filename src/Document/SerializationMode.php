<?php

namespace Zenstruck\Document;

enum SerializationMode
{
    case AsArray;
    case AsDsnString;
    case AsPathString;
}
