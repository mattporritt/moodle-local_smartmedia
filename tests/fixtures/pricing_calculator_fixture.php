<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test fixture for the pricing calculator class.
 *
 * @package     local_smartmedia
 * @copyright   2019 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

return array(
    'readPreset' => array(
        'System preset: HLS Video - 600k.' => array(
            'Preset' => array(
                'Thumbnails' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '192',
                    'Format' => 'png',
                    'PaddingPolicy' => 'NoPad',
                    'Interval' => '300',
                    'MaxHeight' => '108',
                ),
                'Container' => 'ts',
                'Description' => 'System preset: HLS Video - 600k',
                'Video' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '480',
                    'PaddingPolicy' => 'NoPad',
                    'MaxFrameRate' => '60',
                    'FrameRate' => 'auto',
                    'MaxHeight' => '320',
                    'KeyframesMaxDist' => '90',
                    'FixedGOP' => 'true',
                    'Codec' => 'H.264',
                    'Watermarks' => array(
                        0 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopLeft',
                        ),
                        1 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopRight',
                        ),
                        2 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomLeft',
                        ),
                        3 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomRight',
                        ),
                    ),
                    'CodecOptions' => array(
                        'Profile' => 'baseline',
                        'MaxBitRate' => '472',
                        'InterlacedMode' => 'Progressive',
                        'Level' => '3',
                        'ColorSpaceConversionMode' => 'None',
                        'MaxReferenceFrames' => '3',
                        'BufferSize' => '4248',
                    ),
                    'BitRate' => '472',
                    'DisplayAspectRatio' => 'auto',
                ),
                'Type' => 'System',
                'Id' => '1351620000001-200045',
                'Arn' => 'arn:aws:elastictranscoder:ap-southeast-2:512561797349:preset/1351620000001-200045',
                'Name' => 'System preset: HLS Video - 600k',
            ),
        ),
        'System preset: MPEG-Dash Video - 600k.' => array(
            'Preset' => array(
                'Thumbnails' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '192',
                    'Format' => 'png',
                    'PaddingPolicy' => 'NoPad',
                    'Interval' => '300',
                    'MaxHeight' => '108',
                ),
                'Container' => 'fmp4',
                'Description' => 'System preset: MPEG-Dash Video - 600k',
                'Video' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '426',
                    'PaddingPolicy' => 'NoPad',
                    'FrameRate' => '30',
                    'MaxHeight' => '240',
                    'KeyframesMaxDist' => '60',
                    'FixedGOP' => 'true',
                    'Codec' => 'H.264',
                    'Watermarks' => array(
                        0 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopLeft',
                        ),
                        1 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopRight',
                        ),
                        2 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomLeft',
                        ),
                        3 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomRight',
                        ),
                    ),
                    'CodecOptions' => array(
                        'Profile' => 'main',
                        'MaxBitRate' => '600',
                        'InterlacedMode' => 'Progressive',
                        'Level' => '3',
                        'ColorSpaceConversionMode' => 'None',
                        'MaxReferenceFrames' => '1',
                        'BufferSize' => '1200',
                    ),
                    'BitRate' => '600',
                    'DisplayAspectRatio' => 'auto',
                ),
                'Type' => 'System',
                'Id' => '1351620000001-500050',
                'Arn' => 'arn:aws:elastictranscoder:ap-southeast-2:512561797349:preset/1351620000001-500050',
                'Name' => 'System preset: MPEG-Dash Video - 600k',
            ),
        ),
        'System preset: HLS Video - 1M.' => array(
            'Preset' => array(
                'Thumbnails' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '192',
                    'Format' => 'png',
                    'PaddingPolicy' => 'NoPad',
                    'Interval' => '300',
                    'MaxHeight' => '108',
                ),
                'Container' => 'ts',
                'Description' => 'System preset: HLS Video - 1M',
                'Video' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '640',
                    'PaddingPolicy' => 'NoPad',
                    'MaxFrameRate' => '60',
                    'FrameRate' => 'auto',
                    'MaxHeight' => '432',
                    'KeyframesMaxDist' => '90',
                    'FixedGOP' => 'true',
                    'Codec' => 'H.264',
                    'Watermarks' => array(
                        0 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopLeft',
                        ),
                        1 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopRight',
                        ),
                        2 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomLeft',
                        ),
                        3 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomRight',
                        ),
                    ),
                    'CodecOptions' => array(
                        'Profile' => 'main',
                        'MaxBitRate' => '872',
                        'InterlacedMode' => 'Progressive',
                        'Level' => '3',
                        'ColorSpaceConversionMode' => 'None',
                        'MaxReferenceFrames' => '3',
                        'BufferSize' => '7848',
                    ),
                    'BitRate' => '872',
                    'DisplayAspectRatio' => 'auto',
                ),
                'Type' => 'System',
                'Id' => '1351620000001-200035',
                'Arn' => 'arn:aws:elastictranscoder:ap-southeast-2:512561797349:preset/1351620000001-200035',
                'Name' => 'System preset: HLS Video - 1M',
            ),
        ),
        'System preset: MPEG-Dash Video - 1.2M' => array(
            'Preset' => array(
                'Thumbnails' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '192',
                    'Format' => 'png',
                    'PaddingPolicy' => 'NoPad',
                    'Interval' => '300',
                    'MaxHeight' => '108',
                ),
                'Container' => 'fmp4',
                'Description' => 'System preset: MPEG-Dash Video - 1.2M',
                'Video' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '640',
                    'PaddingPolicy' => 'NoPad',
                    'FrameRate' => '30',
                    'MaxHeight' => '360',
                    'KeyframesMaxDist' => '60',
                    'FixedGOP' => 'true',
                    'Codec' => 'H.264',
                    'Watermarks' => array(
                        0 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopLeft',
                        ),
                        1 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopRight',
                        ),
                        2 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomLeft',
                        ),
                        3 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomRight',
                        ),
                    ),
                    'CodecOptions' => array(
                        'Profile' => 'main',
                        'MaxBitRate' => '1200',
                        'InterlacedMode' => 'Progressive',
                        'Level' => '3',
                        'ColorSpaceConversionMode' => 'None',
                        'MaxReferenceFrames' => '3',
                        'BufferSize' => '2400',
                    ),
                    'BitRate' => '1200',
                    'DisplayAspectRatio' => 'auto',
                ),
                'Type' => 'System',
                'Id' => '1351620000001-500040',
                'Arn' => 'arn:aws:elastictranscoder:ap-southeast-2:512561797349:preset/1351620000001-500040',
                'Name' => 'System preset: MPEG-Dash Video - 1.2M',
            ),
        ),
        'System preset: HLS Video - 2M.' => array(
            'Preset' => array(
                'Thumbnails' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '192',
                    'Format' => 'png',
                    'PaddingPolicy' => 'NoPad',
                    'Interval' => '300',
                    'MaxHeight' => '108',
                ),
                'Container' => 'ts',
                'Description' => 'System preset: HLS Video - 2M',
                'Video' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '1024',
                    'PaddingPolicy' => 'NoPad',
                    'MaxFrameRate' => '60',
                    'FrameRate' => 'auto',
                    'MaxHeight' => '768',
                    'KeyframesMaxDist' => '90',
                    'FixedGOP' => 'true',
                    'Codec' => 'H.264',
                    'Watermarks' => array(
                        0 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopLeft',
                        ),
                        1 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopRight',
                        ),
                        2 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomLeft',
                        ),
                        3 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomRight',
                        ),
                    ),
                    'CodecOptions' => array(
                        'Profile' => 'main',
                        'MaxBitRate' => '1872',
                        'InterlacedMode' => 'Progressive',
                        'Level' => '3.1',
                        'ColorSpaceConversionMode' => 'None',
                        'MaxReferenceFrames' => '3',
                        'BufferSize' => '16848',
                    ),
                    'BitRate' => '1872',
                    'DisplayAspectRatio' => 'auto',
                ),
                'Type' => 'System',
                'Id' => '1351620000001-200015',
                'Arn' => 'arn:aws:elastictranscoder:ap-southeast-2:512561797349:preset/1351620000001-200015',
                'Name' => 'System preset: HLS Video - 2M',
            ),
        ),
        'System preset: MPEG-Dash Video - 4.8M.' => array(
            'Preset' => array (
                'Thumbnails' => array (
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '192',
                    'Format' => 'png',
                    'PaddingPolicy' => 'NoPad',
                    'Interval' => '300',
                    'MaxHeight' => '108',
                ),
                'Container' => 'fmp4',
                'Description' => 'System preset: MPEG-Dash Video - 4.8M',
                'Video' => array (
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '1280',
                    'PaddingPolicy' => 'NoPad',
                    'FrameRate' => '30',
                    'MaxHeight' => '720',
                    'KeyframesMaxDist' => '60',
                    'FixedGOP' => 'true',
                    'Codec' => 'H.264',
                    'Watermarks' => array (
                        0 => array (
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopLeft',
                        ),
                        1 => array (
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopRight',
                        ),
                        2 => array (
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomLeft',
                        ),
                        3 => array (
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomRight',
                        ),
                    ),
                    'CodecOptions' => array (
                        'Profile' => 'main',
                        'MaxBitRate' => '4800',
                        'InterlacedMode' => 'Progressive',
                        'Level' => '3.1',
                        'ColorSpaceConversionMode' => 'None',
                        'MaxReferenceFrames' => '3',
                        'BufferSize' => '9600',
                    ),
                    'BitRate' => '4800',
                    'DisplayAspectRatio' => 'auto',
                ),
                'Type' => 'System',
                'Id' => '1351620000001-500020',
                'Arn' => 'arn:aws:elastictranscoder:ap-southeast-2:512561797349:preset/1351620000001-500020',
                'Name' => 'System preset: MPEG-Dash Video - 4.8M',
            ),
        ),
        'System preset: Audio MP3 - 192 kilobits/second.' => array(
            'Preset' => array(
                'Container' => 'mp3',
                'Description' => 'System preset: Audio MP3 - 192 kilobits/second',
                'Audio' => array(
                    'Channels' => '2',
                    'SampleRate' => '44100',
                    'Codec' => 'mp3',
                    'BitRate' => '192',
                ),
                'Type' => 'System',
                'Id' => '1351620000001-300020',
                'Arn' => 'arn:aws:elastictranscoder:ap-southeast-2:512561797349:preset/1351620000001-300020',
                'Name' => 'System preset: Audio MP3 - 192k',
            ),
        ),
        'System preset: Facebook, SmugMug, Vimeo, YouTube.' => array(
            'Preset' => array(
                'Thumbnails' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '192',
                    'Format' => 'png',
                    'PaddingPolicy' => 'NoPad',
                    'Interval' => '60',
                    'MaxHeight' => '108',
                ),
                'Container' => 'mp4',
                'Description' => 'System preset: Facebook, SmugMug, Vimeo, YouTube',
                'Video' => array(
                    'SizingPolicy' => 'ShrinkToFit',
                    'MaxWidth' => '1280',
                    'PaddingPolicy' => 'NoPad',
                    'FrameRate' => '30',
                    'MaxHeight' => '720',
                    'KeyframesMaxDist' => '90',
                    'FixedGOP' => 'false',
                    'Codec' => 'H.264',
                    'Watermarks' => array(
                        0 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopLeft',
                        ),
                        1 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Top',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'TopRight',
                        ),
                        2 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Left',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomLeft',
                        ),
                        3 => array(
                            'SizingPolicy' => 'ShrinkToFit',
                            'VerticalOffset' => '10%',
                            'VerticalAlign' => 'Bottom',
                            'Target' => 'Content',
                            'MaxWidth' => '10%',
                            'MaxHeight' => '10%',
                            'HorizontalAlign' => 'Right',
                            'HorizontalOffset' => '10%',
                            'Opacity' => '100',
                            'Id' => 'BottomRight',
                        ),
                    ),
                    'CodecOptions' => array(
                        'Profile' => 'main',
                        'MaxReferenceFrames' => '3',
                        'ColorSpaceConversionMode' => 'None',
                        'InterlacedMode' => 'Progressive',
                        'Level' => '3.1',
                    ),
                    'BitRate' => '2200',
                    'DisplayAspectRatio' => 'auto',
                ),
                'Audio' => array(
                    'Channels' => '2',
                    'CodecOptions' => array(
                        'Profile' => 'AAC-LC',
                    ),
                    'SampleRate' => '44100',
                    'Codec' => 'AAC',
                    'BitRate' => '160',
                ),
                'Type' => 'System',
                'Id' => '1351620000001-100070',
                'Arn' => 'arn:aws:elastictranscoder:ap-southeast-2:512561797349:preset/1351620000001-100070',
                'Name' => 'System preset: Web',
            ),
        ),
    ),
);