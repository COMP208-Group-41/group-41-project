import React from 'react';
import { Button as rebassButton } from "rebass/styled-components";
import styled from 'styled-components';
import { variant } from 'styled-system';

const Button = styled(rebassButton)((props) => variant({
    variants: {
        solid: {
            //put background color here
        }
    }
    })
);

export default Button;


